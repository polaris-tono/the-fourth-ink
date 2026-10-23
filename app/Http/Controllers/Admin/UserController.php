<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::enum(Role::class)],
        ]);

        $users = User::query()
            ->withCount('articles')
            ->when(filled($data['q'] ?? null), function ($q) use ($data) {
                $like = '%' . addcslashes($data['q'], '%_\\') . '%';
                $q->where(fn ($w) => $w->where('name', 'like', $like)->orWhere('email', 'like', $like));
            })
            ->when($data['role'] ?? null, fn ($q, $r) => $q->where('role', $r))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => Role::cases(),
            'filters' => ['q' => $data['q'] ?? '', 'role' => $data['role'] ?? ''],
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['role' => ['required', Rule::enum(Role::class)]]);

        // An admin can't change their own role, so the site can never be left without an admin.
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot change your own role.']);
        }

        // 'role' is not mass-assignable, so it is set explicitly, only here.
        $user->forceFill(['role' => Role::from($data['role'])])->save();

        return back()->with('status', "{$user->name} is now a {$user->role->label()}.");
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot disable your own account.']);
        }

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('status', $user->is_active ? "{$user->name} was re-enabled." : "{$user->name} was disabled.");
    }
}