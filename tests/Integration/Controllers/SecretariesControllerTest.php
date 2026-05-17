<?php

namespace Tests\Integration\Controllers;

class SecretariesControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\SecretariesController'
        );

        $this->assertStringContainsString('<p>Secretary</p>', $response);
    }
}
