@extends('layouts.app')

@section('title', $category->name)

@section('content')
    <header class="border-b-2 border-ink pb-4">
        <h1 class="font-serif text-4xl font-black">{{ $category->name }}</h1>
        @if ($category->description)
            <p class="mt-1 text-ink-soft">{{ $category->description }}</p>
        @endif
    </header>

    <form method="GET" action="{{ route('categories.show', $category) }}" class="mt-4 flex flex-wrap items-end gap-3">
        <div>
            <label for="type" class="block text-sm font-semibold">Show</label>
            <select id="type" name="type" class="mt-1 border border-ink/40 bg-white px-2 py-1">
                <option value="">All types</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value }}" @selected($type === $t->value)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-ink px-3 py-1 font-semibold text-paper hover:bg-accent">Filter</button>
    </form>

    <section class="mt-6" aria-label="Articles">
        @forelse ($articles as $article)
            <x-article-card :article="$article" />
        @empty
            <p class="py-6 text-ink-soft">No published articles in this category yet.</p>
        @endforelse

        <div class="mt-6">{{ $articles->links() }}</div>
    </section>

    @if ($external->isNotEmpty())
        <section class="mt-12" aria-labelledby="ext-heading">
            <h2 id="ext-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Headlines from around the web</h2>
            <div class="grid gap-x-8 md:grid-cols-2">
                @foreach ($external as $item)
                    <x-external-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection