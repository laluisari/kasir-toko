<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root route should redirect to /admin.
     */
    public function test_the_root_route_redirects_to_admin(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin');
    }

    /**
     * Unauthenticated access to /admin should redirect to login.
     */
    public function test_admin_requires_authentication(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect(route('login'));
    }
}
