<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Guest hitting root should be redirected to login.
     */
    public function test_guests_hitting_root_are_sent_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    /**
     * Authenticated user hitting root should be redirected to the dashboard.
     */
    public function test_authenticated_users_hitting_root_are_sent_to_dashboard(): void
    {
        $admin = \App\Models\User::factory()->make(['role' => 'admin']);

        $this->actingAs($admin)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }
}
