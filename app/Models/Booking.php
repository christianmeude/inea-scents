<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_reference',
        'customer_name',
        'customer_email',
        'customer_phone',
        'package_id',
        'pax',
        'event_date',
        'event_time',
        'venue_address',
        'status',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'event_date' => 'date',
        'total_price' => 'decimal:2',
        'status' => \App\Enums\BookingStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_reference)) {
                $booking->booking_reference = 'BOOKING-' . strtoupper(uniqid());
            }
        });
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
