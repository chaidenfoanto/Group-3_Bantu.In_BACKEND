<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function store() {
        $request->validate([
            'origin' => 'required|array',
            'destination' => 'required|array',
            'destination_name' => 'required|string',
        ]);

        $trip = $request->user()->trips()->create([
            'origin' => $request->origin,
            'destination' => $request->destination,
            'destination_name' => $request->destination_name,
        ]);

        LocationCreated::dispatch($trip, $request->user());

        return response()->json($trip, 201);
    }

    public function start(Request $request, Trip $trip)
    {
        if ($trip->is_started) {
            return response()->json(['message' => 'This tukangs has already started.'], 400);
        }

        $trip->update([
            'is_started' => true,
        ]);

        $trip->load('driver.user');

        LocationStarted::dispatch($trip, $request->user());

        return response()->json($trip, 200);
    }
}
