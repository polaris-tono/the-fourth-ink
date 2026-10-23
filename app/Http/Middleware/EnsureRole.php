<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Usage: ->middleware('role:admin') or ->middleware('role:contributor,admin') */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // A disabled account loses its session immediately.
        if ($user && ! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'This account has been disabled.']);
        }

        abort_unless($user && in_array($user->role->value, $roles, true), 403);

        return $next($request);
    }
}