<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AdminHandoffController extends Controller
{
    public function generateMagicLink(Request $request)
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $token = Str::random(40);
        Cache::put('magic_link_' . $token, $user->id, now()->addMinute());

        $url = URL::temporarySignedRoute(
            'admin.magic.login',
            now()->addMinute(),
            ['token' => $token]
        );

        return response()->json([
            'url' => $url,
        ]);
    }
}
