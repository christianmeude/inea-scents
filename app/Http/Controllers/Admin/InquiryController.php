<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InquiryController extends Controller
{
    private const STATUS_ORDER = [
        'new' => 0,
        'contacted' => 1,
        'booked' => 2,
        'closed' => 3,
    ];

    public function index(Request $request)
    {
        $query = Inquiry::query()
            ->orderByRaw('event_date ASC NULLS LAST')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('archived')) {
            $query->where('archived', $request->boolean('archived'));
        } else {
            $query->where('archived', false);
        }

        return Inertia::render('Inquiries/Index', [
            'inquiries' => $query->paginate(10)->withQueryString(),
            'filters' => $request->only('search', 'status', 'archived'),
        ]);
    }

    public function show(Inquiry $inquiry)
    {
        return Inertia::render('Inquiries/Show', [
            'inquiry' => $inquiry,
        ]);
    }

    public function update(Request $request, Inquiry $inquiry)
    {
        $current = $inquiry->status->value;

        $validated = $request->validate([
            'status' => [
                'sometimes',
                'string',
                function ($attribute, $value, $fail) use ($current) {
                    if ($value === 'booked') {
                        $fail('Booked status is set by the promote flow only.');
                    } elseif (! isset(self::STATUS_ORDER[$value])
                        || self::STATUS_ORDER[$value] < self::STATUS_ORDER[$current]
                    ) {
                        $fail('Status can only move forward.');
                    }
                },
            ],
            'archived' => 'sometimes|boolean',
        ]);

        $inquiry->update($validated);

        return redirect()->route('admin.inquiries.show', $inquiry)->with('success', 'Inquiry updated successfully.');
    }
}
