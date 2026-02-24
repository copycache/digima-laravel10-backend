<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        try {
            $response = $this->get('/');
            $response->assertStatus(200);
        } catch (\Throwable $e) {
            file_put_contents('debug_error.log', $e->getMessage() . "\n" . $e->getTraceAsString());
            throw $e;
        }
    }
}
