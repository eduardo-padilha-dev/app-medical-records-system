<?php

namespace Tests\Integration\Controllers;

class AuthenticationControllerTest extends ControllerTestCase
{
    public function test_render_login_page(): void
    {
        $response = $this->get(
            action: 'login',
            controllerName: 'App\Controllers\AuthenticationController'
        );

        $this->assertMatchesRegularExpression('/<h1[^>]*>\s*Medical Records\s*<\/h1>/', $response);
        $this->assertMatchesRegularExpression('/Acesso seguro ao sistema integrado\./', $response);
        $this->assertMatchesRegularExpression('/id="email"/', $response);
        $this->assertMatchesRegularExpression('/id="password"/', $response);
        $this->assertMatchesRegularExpression('/>\s*Entrar\s*</', $response);
    }
}
