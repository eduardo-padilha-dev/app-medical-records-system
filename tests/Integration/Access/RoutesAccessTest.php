<?php

namespace Tests\Integration\Access;

use App\Models\Admin;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Tests\TestCase;

class RoutesAccessTest extends TestCase
{
    private Client $client;

    public function setUp(): void
    {
        parent::setUp();
        $cookieJar = new CookieJar();
        $this->client = new Client([
            'allow_redirects' => false,
            'base_uri' => 'http://web:8080',
            'cookies' => $cookieJar
        ]);
    }

    public function tearDown(): void
    {
        $user = User::findByEmail('middleware@example.com');
        if ($user) {
            $user->destroy();
        }

        parent::tearDown();
    }

    public function test_protected_routes_should_redirect_to_login(): void
    {
        $routes = ['/', '/admin', '/doctor', '/patient', '/secretary'];

        foreach ($routes as $route) {
            $response = $this->client->get($route);

            $this->assertEquals(
                302,
                $response->getStatusCode(),
                "A rota {$route} não retornou status 302."
            );
            $this->assertEquals(
                '/login',
                $response->getHeader('Location')[0],
                "A rota {$route} não redirecionou para o auth.check."
            );
        }
    }

    public function test_authenticated_user_should_be_redirected_from_login_to_home(): void
    {

        $user = new User([
            'name' => 'Teste Middleware',
            'email' => 'middleware@example.com',
            'cpf' => '00000000000',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $admin = new Admin([
            'user_id' => $user->id,
            'phone' => '11999999999',
        ]);
        $admin->save();

        $this->client->post('/login', [
            'form_params' => [
                'email' => 'middleware@example.com',
                'password' => '123456'
            ]
        ]);

        $response = $this->client->get('/login');

        $this->assertEquals(302, $response->getStatusCode(), 'Não retornou redirect ao acessar o login ja autenticado.');
        $this->assertEquals('/admin', $response->getHeader('Location')[0], 'Não redirecionou para a home.');
    }

    public function test_authenticated_user_dont_acess_restricted_area(): void
    {
        $user = new User([
            'name' => 'Teste Middleware',
            'email' => 'middleware@example.com',
            'cpf' => '00000000000',
            'password' => '123456',
            'password_confirmation' => '123456'
        ]);
        $user->save();

        $admin = new Admin([
            'user_id' => $user->id,
            'phone' => '11999999999',
        ]);
        $admin->save();

        $this->client->post('/login', [
            'form_params' => [
                'email' => 'middleware@example.com',
                'password' => '123456'
            ]
        ]);

        $response = $this->client->get('/patient');

        $this->assertEquals(302, $response->getStatusCode(), 'Não bloqueou o acesso à área restrita de pacientes.');

        $this->assertEquals('/', $response->getHeader('Location')[0], 'Não redirecionou o admin de volta para a home.');
    }
}
