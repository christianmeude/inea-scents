<?php

namespace App\Http\Controllers\Api;

use App\Actions\ConfirmBookingFromWebhook;
use App\Http\Controllers\Controller;
use App\Services\PayMongoSignatureVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function handle(
        Request $request,
        PayMongoSignatureVerifier $verifier,
        ConfirmBookingFromWebhook $confirm,
    ) {
        $rawBody = $request->getContent();

        // Verify-first: no database write happens before the signature
        // checks out. Rejections are logged for admin attention.
        if (! $verifier->verify(
            $rawBody,
            $request->header('Paymongo-Signature'),
            config('services.paymongo.webhook_secret'),
        )) {
            Log::channel('webhook')->warning('PayMongo webhook rejected: invalid signature', [
                'ip' => $request->ip(),
            ]);

            return response()->json(['message' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawBody, true);
        $eventData = is_array($payload) ? ($payload['data'] ?? null) : null;
        $eventId = is_array($eventData) ? ($eventData['id'] ?? null) : null;

        if (! is_array($eventData) || ! is_string($eventId) || $eventId === '') {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventType = $eventData['attributes']['type'] ?? '';

        if ($eventType !== 'link.payment.paid') {
            Log::channel('webhook')->info("PayMongo webhook ignored: unknown type '{$eventType}'", [
                'event_id' => $eventId,
            ]);

            return response()->json(['message' => 'Event ignored']);
        }

        $exists = DB::table('webhook_events')->where('event_id', $eventId)->exists();
        if ($exists) {
            return response()->json(['message' => 'Event already processed']);
        }

        DB::table('webhook_events')->insert([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => $rawBody,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $remarks = $eventData['attributes']['data']['attributes']['remarks'] ?? null;
        if (is_string($remarks) && $remarks !== '') {
            $confirm->execute($remarks);
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
