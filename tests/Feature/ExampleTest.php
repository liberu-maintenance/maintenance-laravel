<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test the root route ("/") returns a successful response.
     */
    public function test_the_root_route_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the "/admin" route redirects unauthenticated users.
     */
    public function test_the_admin_route_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/admin');
        // Filament admin panel redirects to login when unauthenticated
        $response->assertRedirect();
    }
}

