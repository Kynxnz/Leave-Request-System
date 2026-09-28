<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_application_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
