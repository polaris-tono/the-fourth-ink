@extends('layouts.app')

@section('title', 'Editorial desk')

@section('content')
    <header class="border-b-2 border-ink pb-4">
        <p class="font-mono text-xs uppercase tracking-widest text-accent">Editorial desk</p>
        <h1 class="font-serif text-4xl font-black">Dashboard</h1>
    </header>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5" aria-label="Articles by status">
        @foreach ($statuses as $s)
            <a href="{{ route('admin.review.index', ['status' => $s->value]) }}"
               class="block border border-rule bg-white p-4 hover:border-ink">
                <x-status-badge :status="$s" />
                <p class="mt-2 font-serif text-4xl font-black">{{ $counts[$s->value] ?? 0 }}</p>
            </a>
        @endforeach
    </section>

    <div class="mt-10 grid gap-10 lg:grid-cols-2">
        <section aria-labelledby="waiting-heading">
            <h2 id="waiting-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Waiting for review</h2>
            @forelse ($waiting as $item)
                <div class="border-b border-rule py-3">
                    <a href="{{ route('admin.review.show', $item) }}" class="font-serif text-lg font-bold hover:underline">{{ $item->title }}</a>
                    <p class="text-xs text-ink-soft">
                        {{ $item->author->name }} · {{ $item->type->label() }} · {{ $item->category->name }}
                        · submitted {{ $item->submitted_at?->diffForHumans() }}
                    </p>
                </div>
            @empty
                <p class="py-4 text-ink-soft">Nothing is waiting. The queue is clear.</p>
            @endforelse
            <a href="{{ route('admin.review.index') }}" class="mt-3 inline-block text-sm font-semibold underline">Open the full queue →</a>
        </section>

        <section aria-labelledby="activity-heading">
            <h2 id="activity-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">Recent activity</h2>
            @forelse ($activity as $review)
                <div class="border-b border-rule py-3 text-sm">
                    <p>
                        <strong>{{ $review->actor->name }}</strong>
                        moved
                        @if ($review->article)
                            <a href="{{ route('admin.review.show', $review->article) }}" class="underline">“{{ $review->article->title }}”</a>
                        @else
                            an article
                        @endif
                        to <strong>{{ $review->to_status->label() }}</strong>
                    </p>
                    <p class="text-xs text-ink-soft">{{ $review->created_at->diffForHumans() }}</p>
                </div>
            @empty
                <p class="py-4 text-ink-soft">No activity yet.</p>
            @endforelse
        </section>
    </div>
@endsection