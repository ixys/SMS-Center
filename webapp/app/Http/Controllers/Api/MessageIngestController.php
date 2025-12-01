<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformAccount;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MessageIngestController extends Controller
{
    public function incoming(Request $request, PlatformAccount $platformAccount)
    {
        // TODO: sécuriser par signature / token, etc.

        $driver = $platformAccount->driver();

        $message = $driver->handleIncoming($platformAccount, $request->all());

        return response()->json([
            'success' => true,
            'message_id' => $message->id,
        ], Response::HTTP_CREATED);
    }
}
