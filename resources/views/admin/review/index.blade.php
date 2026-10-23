@extends('layouts.app')

@section('title', 'Review queue')

@section('content')
    <header class="border-b-2 border-ink pb-4">
        <a href="{{ route('admin.dashboard') }}" class="text-sm underline">← Dashboard</a>
        <h1 class="mt-2 font-serif text-4xl font-black">Review queue</h1>
    </header>

    <nav aria-label="Filter by status" class="mt-4 flex flex-wrap gap-2 text-sm">
        @foreach ($tabs as $value => $tab)
            <a href="{{ route('admin.review.index', ['status' => $value]) }}"
               class="border border-ink px-3 py-1 {{ $status === $value ? 'bg-ink text-paper' : 'hover:bg-white' }}"
               @if ($status === $value) aria-current="page" @endif>
                {{ $tab['label'] }} ({{ $tab['count'] }})
            </a>
        @endforeach
    </nav>

    <section class="mt-4" aria-label="Articles">
        @forelse ($articles as $item)
            <article class="border-b border-rule py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$item->status" />
                    <span class="font-mono text-xs uppercase text-ink-soft">{{ $item->type->label() }} · {{ $item->category->name }}</span>
                </div>
                <h2 class="mt-1 font-serif text-xl font-bold">
                    <a href="{{ route('admin.review.show', $item) }}" class="hover:underline">{{ $item->title }}</a>
                </h2>
                <p class="text-xs text-ink-soft">
                    By {{ $item->author->name }}
                    @if ($item->submitted_at) · submitted {{ $item->submitted_at->diffForHumans() }} @endif
                </p>
                <a href="{{ route('admin.review.show', $item) }}" class="mt-2 inline-block text-sm font-semibold underline">Open →</a>
            </article>
        @empty
            <p class="py-8 text-ink-soft">No articles here.</p>
        @endforelse

        <div class="mt-6">{{ $articles->links() }}</div>
    </section>
@endsection