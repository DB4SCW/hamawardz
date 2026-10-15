<?php

namespace App\Http\Controllers;

use App\Services\JapaneseCallsignLookup;
use Illuminate\Http\JsonResponse;

class JapaneseCallsignController extends Controller
{
    public function show(string $callsign, JapaneseCallsignLookup $lookup): JsonResponse
    {
        $callsign = strtoupper(trim($callsign));
        $callsign = trim($callsign, '<>;,');

        if (!preg_match('/^(?:J[A-S]|7[J-N]|8[J-N])[0-9][A-Z]{1,4}$/', $callsign)) {
            return response()->json([
                'message' => 'Please provide a valid, standard Japanese callsign.',
            ], 422);
        }

        try {
            $prefectures = $lookup->find($callsign);
        } catch (\Throwable) {
            return response()->json([
                'message' => 'Japanese callsign lookup is not available at the moment.',
            ], 502);
        }

        if ($prefectures === []) {
            return response()->json([
                'message' => 'No WAJA matches found.',
                'callsign' => $callsign,
                'prefectures' => [],
            ], 404);
        }

        return response()->json([
            'callsign' => $callsign,
            'prefectures' => $prefectures,
        ]);
    }
}