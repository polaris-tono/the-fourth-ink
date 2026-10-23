<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@hasSection('title')@yield('title') — @endif{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-paper font-sans text-ink antialiased">

    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-2 focus:top-2 focus:z-50 focus:bg-ink focus:px-3 focus:py-2 focus:text-paper">Skip to content</a>

    {{-- Utility bar --}}
    <div class="border-b border-rule text-sm">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-2">
            <span class="text-ink-soft">{{ now()->format('l, F j, Y') }}</span>
            <div class="flex flex-wrap items-center gap-4">
                @auth
                    <span class="text-ink-soft">
                        {{ auth()->user()->name }}
                        <span class="font-mono text-xs uppercase">({{ auth()->user()->role->label() }})</span>
                    </span>
                    @if (auth()->user()->isAdmin())
                        <a class="underline" href="{{ route('admin.dashboard') }}">Editorial desk</a>
                    @endif
                    @if (auth()->user()->isAdmin() || auth()->user()->isContributor())
                        <a class="underline" href="{{ route('contributor.dashboard') }}">My articles</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="underline">Log out</button>
                    </form>
                @else
                    <a class="underline" href="{{ route('login') }}">Log in</a>
                    <a class="font-semibold underline" href="{{ route('register') }}">Register</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Masthead --}}
    <header>
        <div class="mx-auto max-w-6xl px-4 py-6 text-center">
            <a href="{{ route('home') }}" class="font-serif text-4xl font-black tracking-tight sm:text-6xl">The Fourth Ink</a>
            <p class="mt-1 text-sm text-ink-soft">News · Opinions · Reviews · Analysis · Features</p>
        </div>

        <nav aria-label="Main" class="border-y-2 border-ink">
            <div class="mx-auto flex max-w-6xl items-center gap-2 overflow-x-auto px-4 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <ul class="flex items-center whitespace-nowrap">
                    @foreach ($navCategories as $cat)
                        <li>
                            <a href="{{ url('/category/' . $cat->slug) }}"
                               class="block px-3 py-3 text-sm font-semibold uppercase tracking-wide hover:text-accent">
                                {{ $cat->name }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ url('/writers-block') }}"
                           class="block px-3 py-3 font-mono text-sm uppercase tracking-wide text-block hover:underline">
                            Writers' Block
                        </a>
                    </li>
                </ul>

                <form action="{{ url('/search') }}" method="GET" role="search" class="ml-auto py-2">
                    <label for="q" class="sr-only">Search articles</label>
                    <input id="q" name="q" type="search" placeholder="Search" value="{{ request('q') }}"
                           class="w-32 border border-ink/40 bg-white px-2 py-1 text-sm sm:w-48">
                </form>
            </div>
        </nav>
    </header>

    <main id="main" class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        {{-- Admin sub-navigation (only for admins, only on /admin pages) --}}
        @if (auth()->check() && auth()->user()->isAdmin() && request()->is('admin*'))
            @php
                $adminLinks = [
                    ['admin.dashboard', 'Dashboard', 'admin'],
                    ['admin.review.index', 'Review queue', 'admin/review*'],
                    ['admin.articles.index', 'Articles', 'admin/articles*'],
                    ['admin.users.index', 'Users', 'admin/users*'],
                    ['admin.categories.index', 'Categories', 'admin/categories*'],
                ];
            @endphp
            <nav aria-label="Editorial desk" class="mb-6 flex flex-wrap gap-2 border-b border-rule pb-3 text-sm font-semibold">
                @foreach ($adminLinks as [$route, $label, $pattern])
                    <a href="{{ route($route) }}"
                       class="border border-ink px-3 py-1 {{ request()->is($pattern) ? 'bg-ink text-paper' : 'hover:bg-white' }}"
                       @if (request()->is($pattern)) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </nav>
        @endif

        @if (session('status'))
            <div role="status" class="mb-6 border border-block bg-block-soft px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t-2 border-ink">
        <div class="mx-auto max-w-6xl px-4 py-6 text-sm text-ink-soft">
            <p>© {{ date('Y') }} The Fourth Ink. External news is supplied by third-party sources; links lead to the original publishers.</p>
        </div>
    </footer>
</body>
</html>