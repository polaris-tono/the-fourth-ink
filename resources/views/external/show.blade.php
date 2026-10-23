@extends('layouts.app')

@section('title', $item->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <p class="text-xs font-semibold uppercase tracking-widest text-ink-soft">
            <a href="{{ route('categories.show', $item->category) }}" class="hover:underline">{{ $item->category->name }}</a>
            · {{ $item->source_name ?? 'External source' }}
        </p>

        <h1 class="mt-2 font-serif text-4xl font-black leading-tight">{{ $item->title }}</h1>

        @if ($item->published_at)
            <p class="mt-3 text-sm text-ink-soft">Published {{ $item->published_at->format('F j, Y') }}</p>
        @endif

        @if ($item->description)
            <p class="mt-6 text-lg">{{ $item->description }}</p>
        @endif

        {{-- Data from an outside API is untrusted: only link to real http(s) addresses. --}}
        @if (\Illuminate\Support\Str::startsWith($item->url, ['http://', 'https://']))
            <p class="mt-8">
                <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer nofollow"
                   class="inline-block bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">
                    Read the full story at {{ $item->source_name ?? 'the original publisher' }} →
                </a>
            </p>
        @endif

        <p class="mt-6 border-t border-rule pt-3 text-xs text-ink-soft">
            This headline was supplied by a third-party news service. The Fourth Ink does not republish the full article.
        </p>
    </article>
@endsection