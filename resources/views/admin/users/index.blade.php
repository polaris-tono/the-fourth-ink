@extends('layouts.app')

@section('title', 'Users')

@section('content')
    <header class="border-b-2 border-ink pb-4">
        <p class="font-mono text-xs uppercase tracking-widest text-accent">Editorial desk</p>
        <h1 class="font-serif text-4xl font-black">Users</h1>
        <p class="mt-1 text-sm text-ink-soft">New registrations are readers. Promote a reader to contributor to let them write for Writers' Block.</p>
    </header>

    @error('user')
        <p role="alert" class="mt-4 border border-accent bg-white px-3 py-2 text-sm text-accent">{{ $message }}</p>
    @enderror

    <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="q" class="block text-sm font-semibold">Name or email</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" maxlength="100"
                   class="mt-1 border border-ink/40 bg-white px-3 py-1">
        </div>
        <div>
            <label for="role-filter" class="block text-sm font-semibold">Role</label>
            <select id="role-filter" name="role" class="mt-1 border border-ink/40 bg-white px-2 py-1">
                <option value="">All</option>
                @foreach ($roles as $r)
                    <option value="{{ $r->value }}" @selected($filters['role'] === $r->value)>{{ $r->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-ink px-3 py-1 font-semibold text-paper hover:bg-accent">Filter</button>
    </form>

    <div class="mt-4 overflow-x-auto">
        <table class="w-full min-w-[44rem] text-left text-sm">
            <thead>
                <tr class="border-b-2 border-ink">
                    <th scope="col" class="py-2 pr-3">User</th>
                    <th scope="col" class="py-2 pr-3">Role</th>
                    <th scope="col" class="py-2 pr-3">Articles</th>
                    <th scope="col" class="py-2 pr-3">Account</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $u)
                    @php $isMe = $u->is(auth()->user()); @endphp
                    <tr class="border-b border-rule align-top">
                        <td class="py-3 pr-3">
                            <strong>{{ $u->name }}</strong> @if ($isMe)<span class="text-ink-soft">(you)</span>@endif
                            <br><span class="text-xs text-ink-soft">{{ $u->email }}</span>
                        </td>

                        <td class="py-3 pr-3">
                            @if ($isMe)
                                {{ $u->role->label() }}
                            @else
                                <form method="POST" action="{{ route('admin.users.role', $u) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="role-{{ $u->id }}" class="sr-only">Role for {{ $u->name }}</label>
                                    <select id="role-{{ $u->id }}" name="role" class="border border-ink/40 bg-white px-2 py-1">
                                        @foreach ($roles as $r)
                                            <option value="{{ $r->value }}" @selected($u->role === $r)>{{ $r->label() }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="underline">Save</button>
                                </form>
                            @endif
                        </td>

                        <td class="py-3 pr-3">{{ $u->articles_count }}</td>

                        <td class="py-3 pr-3">
                            <span class="{{ $u->is_active ? 'text-green-800' : 'text-accent' }}">{{ $u->is_active ? 'Active' : 'Disabled' }}</span>
                            @unless ($isMe)
                                <form method="POST" action="{{ route('admin.users.active', $u) }}" class="mt-1"
                                      @if ($u->is_active) onsubmit="return confirm('Disable this account? They will be signed out.')" @endif>
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="underline">{{ $u->is_active ? 'Disable' : 'Enable' }}</button>
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection