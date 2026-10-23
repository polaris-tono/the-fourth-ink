@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="font-serif text-3xl font-bold">Create an account</h1>
        <p class="mt-2 text-sm text-ink-soft">
            New accounts are readers. Contributor access is granted by an editor.
        </p>

        <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-semibold">Name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name" autofocus
                    class="mt-1 w-full border border-ink/40 bg-white px-3 py-2" @error('name') aria-invalid="true"
                    aria-describedby="name-error" @enderror>
                @error('name')
                    <p id="name-error" class="mt-1 text-sm text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    class="mt-1 w-full border border-ink/40 bg-white px-3 py-2" @error('email') aria-invalid="true"
                    aria-describedby="email-error" @enderror>
                @error('email')
                    <p id="email-error" class="mt-1 text-sm text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold">Password</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                    aria-describedby="password-hint" class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                <p id="password-hint" class="mt-1 text-xs text-ink-soft">At least 8 characters, with letters and numbers.
                </p>
                @error('password')
                    <p class="mt-1 text-sm text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    autocomplete="new-password" class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
            </div>

            <button type="submit" class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Register</button>
        </form>

        <p class="mt-6 text-sm text-ink-soft">
            Already registered? <a class="underline" href="{{ route('login') }}">Log in</a>
        </p>

        <p class="mt-2 text-sm text-ink-soft">
            <a class="underline" href="{{ route('home') }}">Back to home</a>
        </p>
    </div>
@endsection