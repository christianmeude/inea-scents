<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MagicLoginController extends Controller
{
    public function login(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired magic link.');
        }

        $token = $request->query('token');
        $userId = Cache::pull('magic_link_' . $token);

        if (! $userId) {
            abort(403, 'Magic link already used or expired.');
        }

        $user = User::find($userId);

        if (! $user || ! $user->is_admin) {
            abort(403, 'Unauthorized.');
        }

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
