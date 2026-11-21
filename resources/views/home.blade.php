@extends('layouts.app')

@section('content')
    @if ($featured->isEmpty() && $latest->isEmpty())
        <p class="text-ink-soft">No stories have been published yet.</p>
    @else
        {{-- Featured --}}
        <section class="grid gap-8 lg:grid-cols-3" aria-labelledby="featured-heading">
            <h2 id="featured-heading" class="sr-only">Featured stories</h2>
            <div class="lg:col-span-2">
                @if ($featured->first())
                    <p class="font-mono text-xs uppercase tracking-widest text-accent">Featured</p>
                    <x-article-card :article="$featured->first()" :lead="true" />
                @endif
            </div>
            <div>
                @foreach ($featured->skip(1) as $story)
                    <x-article-card :article="$story" />
                @endforeach
            </div>
        </section>

        {{-- Latest + Trending --}}
        <section class="mt-12 grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <h2 class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Latest</h2>
                @foreach ($latest as $story)
                    <x-article-card :article="$story" />
                @endforeach
            </div>

            <aside aria-labelledby="trending-heading">
                <h2 id="trending-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Trending</h2>
                <ol class="mt-2">
                    @foreach ($trending as $i => $story)
                        <li class="flex gap-3 border-t border-rule py-3 first:border-t-0">
                            <span class="font-serif text-3xl font-black text-rule" aria-hidden="true">{{ $i + 1 }}</span>
                            <a href="{{ route('articles.show', $story->slug) }}" class="font-serif font-bold leading-snug hover:underline">
                                {{ $story->title }}
                            </a>
                        </li>
                    @endforeach
                </ol>
            </aside>
        </section>
    @endif

    {{-- Writers' Block strip --}}
    @if ($writers->isNotEmpty())
        <section class="mt-12 border-y-2 border-block bg-block-soft px-4 py-8" aria-labelledby="wb-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <h2 id="wb-heading" class="font-mono text-lg uppercase tracking-widest text-block">Writers' Block</h2>
                <a href="{{ route('writers-block') }}" class="text-sm font-semibold text-block underline">All perspectives →</a>
            </div>
            <div class="mt-2 grid gap-x-8 md:grid-cols-3">
                @foreach ($writers as $piece)
                    <x-article-card :article="$piece" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Headlines from external sources (shown only when the news API has supplied some) --}}
    @if ($external->isNotEmpty())
        <section class="mt-12" aria-labelledby="web-heading">
            <h2 id="web-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Headlines from around the web</h2>
            <div class="grid gap-x-8 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($external as $item)
                    <x-external-card :item="$item" />
                @endforeach
            </div>
        </section>
    @endif
@endsection