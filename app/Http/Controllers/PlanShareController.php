<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\PlanShare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlanShareController
{
    public function invite(Request $request, $viewerId)
    {
        $me = $request->user();

        if ((int) $viewerId === $me->id) {
            throw ValidationException::withMessages(['viewer' => ['Nu te poti invita pe tine insuti.']]);
        }

        $viewer = User::findOrFail($viewerId);

        $connected = Connection::where('status', 'accepted')
            ->where(fn ($q) => $q->where('requester_id', $me->id)->where('recipient_id', $viewer->id))
            ->orWhere(fn ($q) => $q->where('requester_id', $viewer->id)->where('recipient_id', $me->id))
            ->exists();

        if (! $connected) {
            throw ValidationException::withMessages(['viewer' => ['Poti invita doar useri cu care esti conectat.']]);
        }

        PlanShare::where('owner_id', $me->id)->where('viewer_id', $viewer->id)->delete();

        $share = PlanShare::create([
            'owner_id' => $me->id,
            'viewer_id' => $viewer->id,
        ]);

        return response()->json($share, 201);
    }

    public function received(Request $request)
    {
        $shares = PlanShare::with('owner')
            ->where('viewer_id', $request->user()->id)
            ->get()
            ->reject(fn (PlanShare $share) => $share->isExpired())
            ->values();

        return response()->json($shares);
    }

    public function trips(Request $request, $ownerId)
    {
        $share = PlanShare::where('owner_id', $ownerId)->where('viewer_id', $request->user()->id)->first();

        if (! $share) {
            return response()->json(['message' => 'Nu ai acces la planurile acestui user.'], 403);
        }

        if ($share->isExpired()) {
            $share->delete();

            return response()->json(['message' => 'Accesul a expirat.'], 403);
        }

        if ($share->first_accessed_at === null) {
            $share->update(['first_accessed_at' => now()]);
        }

        $owner = User::findOrFail($ownerId);

        return response()->json([
            'owner' => ['id' => $owner->id, 'name' => $owner->name],
            'expires_at' => $share->first_accessed_at->addMinutes(PlanShare::EXPIRY_MINUTES),
            'trips' => $owner->trips()->with('activities')->get(),
        ]);
    }
}
