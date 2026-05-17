<?php

namespace Tests\Integration\Controllers;

class AdminsControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\AdminsController'
        );

        $this->assertStringContainsString('<p>Admin</p>', $response);
    }
}
