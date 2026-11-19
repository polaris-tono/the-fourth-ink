@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto max-w-md">
        <h1 class="font-serif text-3xl font-bold">Log in</h1>

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    autofocus class="mt-1 w-full border border-ink/40 bg-white px-3 py-2" @error('email')
                    aria-invalid="true" aria-describedby="email-error" @enderror>
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold">Password</label>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                    class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1"> Remember me
            </label>

            <button type="submit" class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Log in</button>
        </form>

        <p class="mt-6 text-sm text-ink-soft">
            New here? <a class="underline" href="{{ route('register') }}">Create an account</a>
        </p>
    </div>
@endsection