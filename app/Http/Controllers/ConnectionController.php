<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ConnectionController
{
    public function users(Request $request)
    {
        $me = $request->user();

        $users = User::where('id', '!=', $me->id)
            ->when($request->query('search'), function ($query, $search) {
                $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $connections = Connection::where('requester_id', $me->id)
            ->orWhere('recipient_id', $me->id)
            ->get();

        $users->each(function ($user) use ($connections, $me) {
            $connection = $connections->first(fn ($c) => $c->requester_id === $user->id || $c->recipient_id === $user->id);

            $user->connection_status = $connection?->status ?? 'none';
            $user->connection_id = $connection?->id;
            $user->connection_direction = $connection
                ? ($connection->requester_id === $me->id ? 'sent' : 'received')
                : null;
        });

        return response()->json($users);
    }

    public function accepted(Request $request)
    {
        $me = $request->user();

        $connections = Connection::with(['requester', 'recipient'])
            ->where('status', 'accepted')
            ->where(fn ($q) => $q->where('requester_id', $me->id)->orWhere('recipient_id', $me->id))
            ->get();

        $friends = $connections->map(fn ($c) => $c->requester_id === $me->id ? $c->recipient : $c->requester);

        return response()->json($friends->values());
    }

    public function pending(Request $request)
    {
        $connections = Connection::with('requester')
            ->where('recipient_id', $request->user()->id)
            ->where('status', 'pending')
            ->get();

        return response()->json($connections);
    }

    public function sendRequest(Request $request, $recipientId)
    {
        $me = $request->user();

        if ((int) $recipientId === $me->id) {
            throw ValidationException::withMessages(['recipient' => ['Nu te poti conecta cu tine insuti.']]);
        }

        User::findOrFail($recipientId);

        $existing = Connection::where(fn ($q) => $q->where('requester_id', $me->id)->where('recipient_id', $recipientId))
            ->orWhere(fn ($q) => $q->where('requester_id', $recipientId)->where('recipient_id', $me->id))
            ->first();

        if ($existing) {
            throw ValidationException::withMessages(['recipient' => ['Exista deja o conexiune cu acest user.']]);
        }

        $connection = Connection::create([
            'requester_id' => $me->id,
            'recipient_id' => $recipientId,
            'status' => 'pending',
        ]);

        return response()->json($connection, 201);
    }

    public function accept(Request $request, $id)
    {
        $connection = Connection::where('recipient_id', $request->user()->id)->findOrFail($id);
        $connection->update(['status' => 'accepted']);

        return response()->json($connection);
    }

    public function decline(Request $request, $id)
    {
        $connection = Connection::where('recipient_id', $request->user()->id)->findOrFail($id);
        $connection->delete();

        return response()->json(['message' => 'Cerere refuzata.']);
    }
}
