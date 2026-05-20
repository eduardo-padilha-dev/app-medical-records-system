<?php

namespace Tests\Integration\Controllers;

class PatientsControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\PatientsController'
        );

        $this->assertStringContainsString('Painel do Paciente', $response);
    }
}
