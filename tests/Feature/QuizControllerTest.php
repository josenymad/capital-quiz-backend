<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QuizControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_api_returns_valid_response()
    {
        // Mock the external API response
        $mockApiResponse = [
            'data' => [
                ['name' => 'France', 'capital' => 'Paris'],
                ['name' => 'Germany', 'capital' => 'Berlin'],
                ['name' => 'Italy', 'capital' => 'Rome']
            ]
        ];

        // Fake the external API call using Http facade
        Http::fake([
            config('services.countries_api.url') => Http::response($mockApiResponse, 200)
        ]);

        // Call the Laravel API endpoint
        $response = $this->getJson('/quiz');

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the structure of the response JSON
        $response->assertJsonStructure([
            'country',
            'options',
            'correctCapital'
        ]);

        // Verify the response data format
        $responseData = $response->json();
        
        // Assert the country name exists
        $this->assertNotEmpty($responseData['country']);

        // Assert there are 3 options
        $this->assertCount(3, $responseData['options']);

        // Assert the correct capital exists and matches one of the options
        $this->assertContains($responseData['correctCapital'], $responseData['options']);
    }

    public function test_quiz_api_handles_external_api_failure()
    {
        // Fake a failed API call
        Http::fake([
            config('services.countries_api.url') => Http::response(null, 500)
        ]);

        // Call the Laravel API endpoint
        $response = $this->getJson('/quiz');

        // Assert that the response status is 500 (Internal Server Error)
        $response->assertStatus(500);

        // Assert that the error message is returned in the response
        $response->assertJson([
            'error' => 'Unable to generate quiz at this time. Please try again later.'
        ]);
    }

    public function test_quiz_api_uses_cache()
    {
        // Cache mock data to simulate caching
        $cachedData = [
            'data' => [
                ['name' => 'Japan', 'capital' => 'Tokyo'],
                ['name' => 'Brazil', 'capital' => 'Brasilia'],
                ['name' => 'India', 'capital' => 'New Delhi']
            ]
        ];

        Cache::shouldReceive('remember')
            ->once()
            ->with('countries_capitals', 86400, \Closure::class)
            ->andReturn($cachedData);

        // Call the Laravel API endpoint
        $response = $this->getJson('/quiz');

        // Assert that the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the correct data was returned from the cache
        $responseData = $response->json();
        $this->assertEquals('Japan', $responseData['country']);
    }
}