<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QuizController extends Controller
{
    public function getQuiz()
    {
        try {
            // Retrieve API URL from config
            $apiUrl = config('services.countries_api.url');

            if (!$apiUrl) {
                Log::error('External Countries API URL is not set.');
                return response()->json(['error' => 'Configuration error. Please check environment variables.'], 500);
            }

            // Cache the API response for 24 hours to minimize external API calls
            $countriesData = Cache::remember('countries_capitals', 86400, function () use ($apiUrl) {
                $response = Http::get($apiUrl);

                if ($response->failed()) {
                    throw new \Exception('Failed to fetch countries data.');
                }

                return $response->json();
            });

            // Extract the list of countries
            $countries = $countriesData['data'];

            if (empty($countries)) {
                return response()->json(['error' => 'No countries data available.'], 500);
            }

            // Select a random country
            $randomCountry = $countries[array_rand($countries)];
            $correctCapital = $randomCountry['capital'];
            $countryName = $randomCountry['name'];

            // Prepare incorrect capitals
            $incorrectCapitals = [];

            while (count($incorrectCapitals) < 2) {
                $randomIncorrect = $countries[array_rand($countries)]['capital'];
                if ($randomIncorrect !== $correctCapital && !in_array($randomIncorrect, $incorrectCapitals)) {
                    $incorrectCapitals[] = $randomIncorrect;
                }
            }

            // Combine and shuffle the options
            $options = array_merge([$correctCapital], $incorrectCapitals);
            shuffle($options);

            // Return the quiz data
            return response()->json([
                'country' => $countryName,
                'options' => $options,
            ]);

        } catch (\Exception $e) {
            // Log the error for debugging
            Log::error('Quiz API Error: ' . $e->getMessage());

            return response()->json(['error' => 'Unable to generate quiz at this time. Please try again later.'], 500);
        }
    }
}
