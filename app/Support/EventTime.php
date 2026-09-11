<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;

/**
 * Normalizes the free-form event_time coming from admin forms into a
 * Postgres-safe `time` value (`H:i:s`) or null.
 *
 * The mobile client converts Time Slot labels before POSTing and the API
 * enforces `date_format:H:i:s`, but admin inputs are free text — a label
 * or stray value used to reach the insert and 500 on the `time` column.
 * Centralizing here (per ADR-0002) protects every caller at once:
 * API store, admin create, and inquiry promote.
 */
class EventTime
{
    /**
     * Time Slot labels served by the booking UI, mapped to start times.
     * Mirrors the mobile `TimeSlot.available` list — keep in sync.
     */
    public const SLOT_LABELS = [
        '10:00 AM - 1:00 PM' => '10:00:00',
        '2:00 PM - 5:00 PM' => '14:00:00',
        '6:00 PM - 9:00 PM' => '18:00:00',
    ];

    /**
     * @throws ValidationException on non-empty, unparseable values.
     */
    public static function normalize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (isset(self::SLOT_LABELS[$value])) {
            return self::SLOT_LABELS[$value];
        }

        foreach (['H:i:s', 'H:i', 'g:i A', 'h:i A'] as $format) {
            $parsed = date_create_from_format($format, $value);
            // date_get_last_errors() returns false when the parse is
            // completely clean — that is the success case, not a failure.
            $errors = date_get_last_errors();
            $clean = $errors === false
                || ($errors['warning_count'] === 0 && $errors['error_count'] === 0);
            if ($parsed !== false && $clean) {
                return $parsed->format('H:i:s');
            }
        }

        throw ValidationException::withMessages([
            'event_time' => ['Enter a valid time (e.g. 14:00) or pick a time slot.'],
        ]);
    }
}
