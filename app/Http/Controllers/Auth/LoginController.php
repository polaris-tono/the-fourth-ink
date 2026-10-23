<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Only active accounts can log in.
        $ok = Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $ok) {
            throw ValidationException::withMessages([
                'email' => 'These credentials do not match our records, or the account is disabled.',
            ]);
        }

        $request->session()->regenerate(); // prevents session fixation

        return redirect()->intended($this->homeFor(Auth::user()->role));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }

    private function homeFor(Role $role): string
    {
        return match ($role) {
            Role::Admin => route('admin.dashboard'),
            Role::Contributor => route('contributor.dashboard'),
            default => route('home'),
        };
    }
}