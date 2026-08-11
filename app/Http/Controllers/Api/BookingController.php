<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use OpenApi\Attributes as OAT;

#[OAT\Tag(
    name: 'Bookings',
    description: 'API Endpoints for User Bookings'
)]
class BookingController extends Controller
{
    #[OAT\Get(
        path: '/api/bookings',
        summary: "Get user's bookings",
        description: 'Returns a list of all bookings for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Bookings']
    )]
    #[OAT\Response(
        response: 200,
        description: 'Successful operation',
        content: new OAT\JsonContent(
            type: 'array',
            items: new OAT\Items(
                properties: [
                    new OAT\Property(property: 'id', type: 'integer'),
                    new OAT\Property(property: 'booking_reference', type: 'string'),
                    new OAT\Property(property: 'user_id', type: 'integer'),
                    new OAT\Property(property: 'customer_name', type: 'string'),
                    new OAT\Property(property: 'status', type: 'string'),
                    new OAT\Property(property: 'event_date', type: 'string', format: 'date'),
                    new OAT\Property(property: 'payment_method', type: 'string'),
                    new OAT\Property(property: 'package', ref: '#/components/schemas/Package'),
                    new OAT\Property(property: 'scents', type: 'array', items: new OAT\Items(ref: '#/components/schemas/Scent')),
                ],
                type: 'object'
            )
        )
    )]
    public function index(Request $request)
    {
        $user = $request->user();
        $bookings = $user->bookings()->with(['package', 'scents'])->get();

        return response()->json($bookings);
    }

    #[OAT\Post(
        path: '/api/bookings',
        summary: 'Create a new booking',
        description: 'Creates a new booking for the authenticated user',
        security: [['sanctum' => []]],
        tags: ['Bookings']
    )]
    #[OAT\RequestBody(
        required: true,
        content: new OAT\JsonContent(
            required: ['package_id', 'customer_name', 'event_date', 'venue_address', 'payment_method'],
            properties: [
                new OAT\Property(property: 'package_id', type: 'integer'),
                new OAT\Property(property: 'customer_name', type: 'string'),
                new OAT\Property(property: 'customer_email', type: 'string'),
                new OAT\Property(property: 'customer_phone', type: 'string'),
                new OAT\Property(property: 'pax', type: 'integer'),
                new OAT\Property(property: 'event_date', type: 'string', format: 'date'),
                new OAT\Property(property: 'event_time', type: 'string', format: 'time'),
                new OAT\Property(property: 'venue_address', type: 'string'),
                new OAT\Property(property: 'payment_method', type: 'string'),
                new OAT\Property(property: 'scent_ids', type: 'array', items: new OAT\Items(type: 'integer')),
            ]
        )
    )]
    #[OAT\Response(
        response: 201,
        description: 'Booking created successfully',
        content: new OAT\JsonContent(
            properties: [
                new OAT\Property(property: 'id', type: 'integer'),
                new OAT\Property(property: 'booking_reference', type: 'string'),
                new OAT\Property(property: 'user_id', type: 'integer'),
                new OAT\Property(property: 'customer_name', type: 'string'),
                new OAT\Property(property: 'status', type: 'string'),
                new OAT\Property(property: 'event_date', type: 'string', format: 'date'),
                new OAT\Property(property: 'payment_method', type: 'string'),
                new OAT\Property(property: 'package', ref: '#/components/schemas/Package'),
                new OAT\Property(property: 'scents', type: 'array', items: new OAT\Items(ref: '#/components/schemas/Scent')),
            ],
            type: 'object'
        )
    )]
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
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'scent_ids' => 'nullable|array',
            'scent_ids.*' => 'exists:scents,id',
        ]);

        $user = $request->user();
        $booking = $user->bookings()->create($validated);

        if (! empty($validated['scent_ids'])) {
            $booking->scents()->attach($validated['scent_ids']);
        }

        return response()->json($booking->load(['package', 'scents']), 201);
    }
}
