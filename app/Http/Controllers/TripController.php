<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class TripController
{
    public function index(Request $request)
    {
        return response()->json($request->user()->trips()->with('activities')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'numit_destinatie' => 'required|string|max:255',
            'data_inceput' => 'nullable|date',
            'data_sfarsit' => 'nullable|date',
        ]);

        $trip = $request->user()->trips()->create($validated);

        return response()->json($trip, 201);
    }

    public function destroy(Request $request, $id)
    {
        $trip = $request->user()->trips()->findOrFail($id);
        $trip->delete();

        return response()->json(['message' => 'Calatorie stearsa cu succes!']);
    }

    public function update(Request $request, $id)
    {
        $trip = $request->user()->trips()->findOrFail($id);

        $validated = $request->validate([
            'numit_destinatie' => 'required|string|max:255',
            'data_inceput' => 'nullable|date',
            'data_sfarsit' => 'nullable|date',
        ]);

        $trip->update($validated);

        return response()->json($trip);
    }

    public function addActivity(Request $request, $tripId)
    {
        $trip = $request->user()->trips()->findOrFail($tripId);

        $validated = $request->validate([
            'titlu_activitate' => 'required|string|max:255',
            'tip' => 'nullable|string|in:obiectiv,gastronomie,altele',
            'descriere' => 'nullable|string',
            'ora' => 'nullable',
        ]);

        $activity = $trip->activities()->create([
            'titlu_activitate' => $validated['titlu_activitate'],
            'tip' => $validated['tip'] ?? 'altele',
            'descriere' => $validated['descriere'] ?? null,
            'ora' => $validated['ora'] ?? null,
            'bifat' => false,
        ]);

        return response()->json($activity, 201);
    }

    public function updateActivity(Request $request, $activityId)
    {
        $activity = Activity::whereHas('trip', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($activityId);

        $validated = $request->validate([
            'titlu_activitate' => 'sometimes|required|string|max:255',
            'tip' => 'nullable|string|in:obiectiv,gastronomie,altele',
            'descriere' => 'nullable|string',
            'ora' => 'nullable',
            'bifat' => 'sometimes|boolean',
        ]);

        $activity->update($validated);

        return response()->json($activity);
    }

    public function deleteActivity(Request $request, $activityId)
    {
        $activity = Activity::whereHas('trip', fn ($query) => $query->where('user_id', $request->user()->id))
            ->findOrFail($activityId);
        $activity->delete();

        return response()->json(['message' => 'Activitate stearsa cu succes!']);
    }
}
