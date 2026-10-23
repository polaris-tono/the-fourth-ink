@extends('layouts.app')

@section('title', 'My articles')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-block pb-4">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-block">Writers' Block</p>
            <h1 class="font-mono text-3xl uppercase text-block">My desk</h1>
        </div>
        <a href="{{ route('contributor.articles.create') }}" class="bg-block px-4 py-2 font-semibold text-paper hover:bg-ink">+ New article</a>
    </header>

    @error('status')
        <p role="alert" class="mt-4 border border-accent bg-white px-3 py-2 text-sm text-accent">{{ $message }}</p>
    @enderror

    @forelse ($articles as $article)
        <article class="border-b border-rule py-4">
            <div class="flex flex-wrap items-center gap-2">
                <x-status-badge :status="$article->status" />
                <span class="font-mono text-xs uppercase text-ink-soft">{{ $article->type->label() }} · {{ $article->category->name }}</span>
            </div>

            <h2 class="mt-1 font-serif text-xl font-bold">
                <a href="{{ route('contributor.articles.show', $article) }}" class="hover:underline">{{ $article->title }}</a>
            </h2>
            <p class="text-xs text-ink-soft">Last updated {{ $article->updated_at->diffForHumans() }}</p>

            @if ($article->status === \App\Enums\ArticleStatus::RevisionRequested && $article->reviews->first())
                <p class="mt-2 border-l-4 border-orange-600 bg-orange-50 px-3 py-2 text-sm">
                    <strong>Editor's feedback:</strong> {{ $article->reviews->first()->comment }}
                </p>
            @endif

            <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                <a href="{{ route('contributor.articles.show', $article) }}" class="underline">View</a>

                @can('update', $article)
                    <a href="{{ route('contributor.articles.edit', $article) }}" class="underline">Edit</a>
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
            </div>
        </article>
    @empty
        <p class="py-8 text-ink-soft">You haven't written anything yet. Start with “New article”.</p>
    @endforelse

    <div class="mt-6">{{ $articles->links() }}</div>
@endsection