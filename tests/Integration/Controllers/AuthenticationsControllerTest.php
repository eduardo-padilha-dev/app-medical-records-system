<?php

namespace Tests\Integration\Controllers;

class AuthenticationsControllerTest extends ControllerTestCase
{
    public function test_render_login_page(): void
    {
        $response = $this->get(
            action: 'new',
            controllerName: 'App\Controllers\AuthenticationsController'
        );

        $this->assertStringContainsString('Medical Records', $response);
        $this->assertStringContainsString('Acesso ao Sistema Integrado', $response);
        $this->assertStringContainsString('id="email"', $response);
        $this->assertStringContainsString('id="password"', $response);
        $this->assertStringContainsString('Entrar', $response);
    }
}
