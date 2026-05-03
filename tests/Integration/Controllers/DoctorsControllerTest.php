<?php

namespace Tests\Integration\Controllers;

class DoctorsControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\DoctorsController'
        );

        $this->assertStringContainsString('<p>Doctor</p>', $response);
    }
}
