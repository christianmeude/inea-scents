<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Bookings",
 *     description="API Endpoints for User Bookings"
 * )
 */
class BookingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bookings",
     *     summary="Get user's bookings",
     *     description="Returns a list of all bookings for the authenticated user",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="array",
     *
     *             @OA\Items(
     *                 type="object",
     *
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="booking_reference", type="string"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="customer_name", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *                 @OA\Property(property="event_date", type="string", format="date"),
     *                 @OA\Property(property="payment_method", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['package', 'scents'])->get();

        return response()->json($bookings);
    }

    /**
     * @OA\Post(
     *     path="/api/bookings",
     *     summary="Create a new booking",
     *     description="Creates a new booking for the authenticated user",
     *     tags={"Bookings"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"package_id", "customer_name", "event_date", "venue_address"},
     *
     *             @OA\Property(property="package_id", type="integer"),
     *             @OA\Property(property="customer_name", type="string"),
     *             @OA\Property(property="customer_email", type="string"),
     *             @OA\Property(property="customer_phone", type="string"),
     *             @OA\Property(property="pax", type="integer"),
     *             @OA\Property(property="event_date", type="string", format="date"),
     *             @OA\Property(property="event_time", type="string", format="time"),
     *             @OA\Property(property="venue_address", type="string"),
     *             @OA\Property(property="payment_method", type="string"),
     *             @OA\Property(property="scent_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Booking created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email|max:255',
            'customer_phone' => 'nullable|string|max:255',
            'pax' => 'nullable|integer|min:1',
            'event_date' => 'required|date',
            'event_time' => 'nullable|date_format:H:i:s',
            'venue_address' => 'required|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'scent_ids' => 'nullable|array',
            'scent_ids.*' => 'exists:scents,id',
        ]);

        $booking = $request->user()->bookings()->create($validated);

        if (! empty($validated['scent_ids'])) {
            $booking->scents()->attach($validated['scent_ids']);
        }

        return response()->json($booking->load(['package', 'scents']), 201);
    }
}
