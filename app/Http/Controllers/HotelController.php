<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HotelController
{
    public function search(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string|max:255',
        ]);

        $response = Http::withHeaders([
            'x-rapidapi-host' => config('services.rapidapi.host'),
            'x-rapidapi-key' => config('services.rapidapi.key'),
        ])->get('https://travel-advisor.p.rapidapi.com/locations/search', [
            'query' => $validated['query'],
            'limit' => 10,
            'lang' => 'en_US',
            'category' => 'hotels',
        ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Cautarea de cazari a esuat.'], 502);
        }

        $hotels = collect($response->json('data', []))
            ->map(function ($item) {
                $result = $item['result_object'] ?? [];

                return [
                    'nume' => $result['name'] ?? null,
                    'rating' => $result['rating'] ?? null,
                    'poza' => $result['photo']['images']['medium']['url'] ?? null,
                ];
            })
            ->filter(fn ($hotel) => $hotel['nume'])
            ->values();

        return response()->json($hotels);
    }
}