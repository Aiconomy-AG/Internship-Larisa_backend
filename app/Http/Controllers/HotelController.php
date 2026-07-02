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
            'checkin' => 'required|date|after_or_equal:today',
            'checkout' => 'required|date|after:checkin',
            'adults' => 'nullable|integer|min:1|max:10',
        ]);

        $headers = [
            'x-rapidapi-host' => config('services.rapidapi.host_booking'),
            'x-rapidapi-key' => config('services.rapidapi.key'),
        ];

        $destination = Http::withHeaders($headers)
            ->get('https://booking-com15.p.rapidapi.com/api/v1/hotels/searchDestination', [
                'query' => $validated['query'],
            ]);

        if ($destination->failed() || empty($destination->json('data.0'))) {
            return response()->json(['message' => 'Destinatia nu a fost gasita.'], 404);
        }

        $destId = $destination->json('data.0.dest_id');
        $searchType = $destination->json('data.0.search_type');

        $response = Http::withHeaders($headers)
            ->get('https://booking-com15.p.rapidapi.com/api/v1/hotels/searchHotels', [
                'dest_id' => $destId,
                'search_type' => $searchType,
                'arrival_date' => $validated['checkin'],
                'departure_date' => $validated['checkout'],
                'adults' => $validated['adults'] ?? 2,
                'room_qty' => 1,
                'currency_code' => 'EUR',
            ]);

        if ($response->failed()) {
            return response()->json(['message' => 'Cautarea de cazari a esuat.'], 502);
        }

        $hotels = collect($response->json('data.hotels', []))
            ->map(function ($item) {
                $property = $item['property'] ?? [];

                return [
                    'nume' => $property['name'] ?? null,
                    'rating' => $property['reviewScore'] ?? null,
                    'poza' => $property['photoUrls'][0] ?? null,
                    'pret' => $property['priceBreakdown']['grossPrice']['value'] ?? null,
                    'moneda' => $property['priceBreakdown']['grossPrice']['currency'] ?? null,
                ];
            })
            ->filter(fn ($hotel) => $hotel['nume'])
            ->take(10)
            ->values();

        return response()->json($hotels);
    }
}