@extends('layouts.app')

@section('title', 'Search')

@section('content')
    <h1 class="font-serif text-4xl font-black">Search</h1>

    <form method="GET" action="{{ route('search') }}" class="mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="search-q" class="block text-sm font-semibold">Keywords</label>
            <input id="search-q" name="q" type="search" value="{{ $q }}" maxlength="100"
                   class="mt-1 w-64 border border-ink/40 bg-white px-3 py-2">
        </div>
        <div>
            <label for="search-cat" class="block text-sm font-semibold">Category</label>
            <select id="search-cat" name="category" class="mt-1 border border-ink/40 bg-white px-2 py-2">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->slug }}" @selected($categorySlug === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Search</button>
    </form>

    @error('q')<p class="mt-2 text-sm text-accent">{{ $message }}</p>@enderror

    @if ($q !== '')
        <section class="mt-8" aria-label="Results">
            <h2 class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">
                Articles for “{{ $q }}”
            </h2>
            @forelse ($articles as $article)
                <x-article-card :article="$article" />
            @empty
                <p class="py-4 text-ink-soft">No articles matched your search.</p>
            @endforelse
            <div class="mt-6">{{ $articles->links() }}</div>
        </section>

        @if ($external->isNotEmpty())
            <section class="mt-10">
                <h2 class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Headlines from around the web</h2>
                <div class="grid gap-x-8 md:grid-cols-2">
                    @foreach ($external as $item)
                        <article class="border-b border-rule py-4">
                            @if ($item->image_url)
                                <img
                                    src="{{ $item->image_url }}"
                                    alt="{{ $item->title }}"
                                    loading="lazy"
                                    onerror="this.style.display='none'"
                                    class="mb-3 h-44 w-full rounded object-cover"
                                >
                            @endif
                            <x-external-card :item="$item" />
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
@endsection