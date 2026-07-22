<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $request->user()->updatePushSubscription(
            endpoint: $datos['endpoint'],
            key: $datos['keys']['p256dh'],
            token: $datos['keys']['auth'],
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $request->user()->deletePushSubscription($datos['endpoint']);

        return response()->json(['ok' => true]);
    }
}
