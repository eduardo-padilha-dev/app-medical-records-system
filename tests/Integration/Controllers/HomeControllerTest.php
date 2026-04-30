<?php

namespace Tests\Integration\Controllers;

class HomeControllerTest extends ControllerTestCase
{
    public function test_render_home_page(): void
    {
        $response = $this->get(
            action: 'index',
            controllerName: 'App\Controllers\HomeController'
        );

        $this->assertMatchesRegularExpression('/<p>\s*TSI3D Framework Template\s*<\/p>/', $response);
        $this->assertMatchesRegularExpression('/TSI3D Framework Template/', $response);
    }
}
