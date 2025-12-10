<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    /**
     * Test que l'application retourne une réponse réussie.
     */
    public function test_application_returns_successful_response(): void
    {
        $response = $this->get('/');

        // L'application redirige vers la page de login si l'utilisateur n'est pas authentifié
        $response->assertStatus(302);
    }

    /**
     * Test du endpoint de santé de l'application.
     */
    public function test_health_check_endpoint(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }
}