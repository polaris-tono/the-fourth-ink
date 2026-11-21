@extends('layouts.app')

@section('title', $article->title)

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('contributor.dashboard') }}" class="text-sm underline">← Back to my articles</a>

        @error('status')
            <p role="alert" class="mt-4 border border-accent bg-white px-3 py-2 text-sm text-accent">{{ $message }}</p>
        @enderror

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <x-status-badge :status="$article->status" />
            <span class="font-mono text-xs uppercase text-ink-soft">{{ $article->type->label() }} · {{ $article->category->name }}</span>
        </div>

        <h1 class="mt-2 font-serif text-4xl font-black leading-tight">{{ $article->title }}</h1>
        @if ($article->excerpt)
            <p class="mt-2 text-lg text-ink-soft">{{ $article->excerpt }}</p>
        @endif

        <div class="mt-4 flex flex-wrap items-center gap-4 border-y border-rule py-3 text-sm">
            @can('update', $article)
                <a href="{{ route('contributor.articles.edit', $article) }}" class="font-semibold underline">Edit</a>
            @endcan

            @can('submit', $article)
                <form method="POST" action="{{ route('contributor.articles.submit', $article) }}">
                    @csrf
                    <button type="submit" class="font-semibold text-block underline">Submit for review</button>
                </form>
            @endcan

            @can('delete', $article)
                <form method="POST" action="{{ route('contributor.articles.destroy', $article) }}"
                      onsubmit="return confirm('Delete this draft?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-accent underline">Delete</button>
                </form>
            @endcan

            @if ($article->status === \App\Enums\ArticleStatus::Published)
                <a href="{{ route('articles.show', $article->slug) }}" class="underline">View on the public site →</a>
            @endif
        </div>

        @if ($article->coverUrl())
            <figure class="mt-6">
                <img src="{{ $article->coverUrl() }}" alt="{{ $article->cover_alt }}" class="max-h-96 w-full rounded object-cover">
            </figure>
        @endif

        {{-- bodyHtml() strips raw HTML and unsafe links --}}
        <div class="article-body mt-6">{!! $article->bodyHtml() !!}</div>

        <section class="mt-10 border-t-2 border-block pt-4" aria-labelledby="history-heading">
            <h2 id="history-heading" class="font-mono text-lg uppercase tracking-widest text-block">Editorial history</h2>

            @forelse ($article->reviews as $review)
                <div class="border-b border-rule py-3 text-sm">
                    <p class="text-ink-soft">
                        {{ $review->created_at->format('M j, Y g:i A') }} ·
                        <strong class="text-ink">{{ $review->actor->name }}</strong>:
                        {{ $review->from_status?->label() ?? 'New' }} → <strong class="text-ink">{{ $review->to_status->label() }}</strong>
                    </p>
                    @if ($review->comment)
                        <blockquote class="mt-1 border-l-4 border-block pl-3">{{ $review->comment }}</blockquote>
                    @endif
                </div>
            @empty
                <p class="py-3 text-sm text-ink-soft">Nothing has happened yet. History appears here once you submit.</p>
            @endforelse
        </section>
    </div>
@endsection