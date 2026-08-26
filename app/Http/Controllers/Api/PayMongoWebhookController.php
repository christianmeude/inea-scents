<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();
        $eventData = $payload['data'] ?? null;

        if (!$eventData) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $eventId = $eventData['id'];
        $eventType = $eventData['attributes']['type'] ?? '';

        // Idempotency check
        $exists = DB::table('webhook_events')->where('event_id', $eventId)->exists();
        if ($exists) {
            return response()->json(['message' => 'Event already processed']);
        }

        DB::table('webhook_events')->insert([
            'event_id' => $eventId,
            'event_type' => $eventType,
            'payload' => json_encode($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($eventType === 'link.payment.paid') {
            $remarks = $eventData['attributes']['data']['attributes']['remarks'] ?? null;
            if ($remarks) {
                $booking = Booking::where('booking_reference', $remarks)->first();
                if ($booking && $booking->status === BookingStatus::Pending) {
                    $booking->update(['status' => BookingStatus::Confirmed]);
                    Log::info("Booking {$remarks} confirmed via PayMongo Webhook");
                }
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }
}
