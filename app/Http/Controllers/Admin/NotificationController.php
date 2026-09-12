<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'notifications' => $user->notifications()->latest()->limit(10)->get()->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->data['type'] ?? $n->type,
                'title' => $n->data['title'] ?? '',
                'body' => $n->data['body'] ?? '',
                'link' => $n->data['link'] ?? null,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at,
            ]),
        ]);
    }

    public function read(Request $request)
    {
        $validated = $request->validate(['ids' => 'nullable|array', 'ids.*' => 'string']);

        $query = $request->user()->unreadNotifications();

        if (! empty($validated['ids'])) {
            $query->whereIn('id', $validated['ids']);
        }

        $query->update(['read_at' => now()]);

        return response()->json(['message' => 'Notifications marked as read.']);
    }
}
