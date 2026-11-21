@extends('layouts.app')

@section('title', 'Review: ' . $article->title)

@section('content')
    @php
        use App\Enums\ArticleStatus;

        // The admin's possible moves from the current status (Draft→Pending is the author's move).
        $moves = collect($article->status->allowedTransitions())
            ->reject(fn ($s) => $s === ArticleStatus::PendingReview);

        $label = fn ($to) => match (true) {
            $to === ArticleStatus::Approved && $article->status === ArticleStatus::Published => 'Unpublish',
            $to === ArticleStatus::Approved => 'Approve',
            $to === ArticleStatus::RevisionRequested => 'Request revision',
            $to === ArticleStatus::Rejected => 'Reject',
            $to === ArticleStatus::Published => 'Publish',
        };

        $buttonClass = fn ($to) => match ($to) {
            ArticleStatus::Rejected => 'bg-accent text-paper hover:bg-ink',
            ArticleStatus::Published, ArticleStatus::Approved => 'bg-ink text-paper hover:bg-block',
            default => 'border-2 border-ink text-ink hover:bg-white',
        };
    @endphp

    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.review.index') }}" class="text-sm underline">← Review queue</a>

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
        <p class="mt-3 border-y border-rule py-2 text-sm text-ink-soft">
            By <strong class="text-ink">{{ $article->author->name }}</strong>
            @if ($article->submitted_at) · submitted {{ $article->submitted_at->format('M j, Y g:i A') }} @endif
            @if ($article->status === ArticleStatus::Published)
                · <a href="{{ route('articles.show', $article->slug) }}" class="underline">View on the public site →</a>
            @endif
        </p>

        {{-- Decision panel --}}
        <section class="mt-6 border-2 border-ink bg-white p-4" aria-labelledby="decision-heading">
            <h2 id="decision-heading" class="font-serif text-xl font-bold">Decision</h2>

            @forelse ($moves as $to)
                <form method="POST" action="{{ route('admin.review.transition', $article) }}" class="mt-4 border-t border-rule pt-4">
                    @csrf
                    <input type="hidden" name="to" value="{{ $to->value }}">

                    @if ($to->requiresComment())
                        <label for="comment-{{ $to->value }}" class="block text-sm font-semibold">
                            {{ $to === ArticleStatus::Rejected ? 'Reason for rejection (required)' : 'What needs to change? (required)' }}
                        </label>
                        <textarea id="comment-{{ $to->value }}" name="comment" rows="3" required maxlength="2000"
                                  class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">{{ old('to') === $to->value ? old('comment') : '' }}</textarea>
                        @if (old('to') === $to->value)
                            @error('comment')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                        @endif
                    @elseif ($to === ArticleStatus::Approved && $article->status === ArticleStatus::PendingReview)
                        <label for="comment-{{ $to->value }}" class="block text-sm font-semibold">
                            Note to the writer <span class="font-normal text-ink-soft">(optional)</span>
                        </label>
                        <textarea id="comment-{{ $to->value }}" name="comment" rows="2" maxlength="2000"
                                  class="mt-1 w-full border border-ink/40 bg-white px-3 py-2"></textarea>
                    @endif

                    <button type="submit" class="mt-3 px-4 py-2 font-semibold {{ $buttonClass($to) }}">{{ $label($to) }}</button>
                </form>
            @empty
                <p class="mt-3 text-sm text-ink-soft">
                    @if ($article->status === ArticleStatus::Rejected)
                        This article was rejected. That decision is final; the writer can start a new article.
                    @else
                        Nothing to decide right now. The article is with the writer.
                    @endif
                </p>
            @endforelse
        </section>

        @if ($article->coverUrl())
            <figure class="mt-6">
                <img src="{{ $article->coverUrl() }}" alt="{{ $article->cover_alt }}" class="max-h-96 w-full rounded object-cover">
            </figure>
        @endif

        {{-- Full text --}}
        <div class="article-body mt-8">{!! $article->bodyHtml() !!}</div>

        {{-- History --}}
        <section class="mt-10 border-t-2 border-ink pt-4" aria-labelledby="history-heading">
            <h2 id="history-heading" class="font-serif text-2xl font-bold">Editorial history</h2>
            @forelse ($article->reviews as $review)
                <div class="border-b border-rule py-3 text-sm">
                    <p class="text-ink-soft">
                        {{ $review->created_at->format('M j, Y g:i A') }} ·
                        <strong class="text-ink">{{ $review->actor->name }}</strong>:
                        {{ $review->from_status?->label() ?? 'New' }} → <strong class="text-ink">{{ $review->to_status->label() }}</strong>
                    </p>
                    @if ($review->comment)
                        <blockquote class="mt-1 border-l-4 border-ink pl-3">{{ $review->comment }}</blockquote>
                    @endif
                </div>
            @empty
                <p class="py-3 text-sm text-ink-soft">No history yet.</p>
            @endforelse
        </section>
    </div>
@endsection