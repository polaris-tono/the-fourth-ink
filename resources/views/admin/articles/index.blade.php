@extends('layouts.app')

@section('title', 'Articles')

@section('content')
    <header class="flex flex-wrap items-end justify-between gap-3 border-b-2 border-ink pb-4">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-accent">Editorial desk</p>
            <h1 class="font-serif text-4xl font-black">{{ $trash ? 'Trash' : 'Articles' }}</h1>
        </div>
        <div class="flex gap-3 text-sm font-semibold">
            @if ($trash)
                <a href="{{ route('admin.articles.index') }}" class="underline">← Back to articles</a>
            @else
                <a href="{{ route('admin.articles.index', ['trash' => 1]) }}" class="underline">Trash</a>
                <a href="{{ route('admin.articles.create') }}" class="bg-ink px-4 py-2 text-paper hover:bg-accent">+ New article</a>
            @endif
        </div>
    </header>

    <form method="GET" action="{{ route('admin.articles.index') }}" class="mt-4 flex flex-wrap items-end gap-3">
        @if ($trash)<input type="hidden" name="trash" value="1">@endif
        <div>
            <label for="q" class="block text-sm font-semibold">Title contains</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" maxlength="100"
                   class="mt-1 border border-ink/40 bg-white px-3 py-1">
        </div>
        <div>
            <label for="status" class="block text-sm font-semibold">Status</label>
            <select id="status" name="status" class="mt-1 border border-ink/40 bg-white px-2 py-1">
                <option value="">All</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected($filters['status'] === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="type" class="block text-sm font-semibold">Type</label>
            <select id="type" name="type" class="mt-1 border border-ink/40 bg-white px-2 py-1">
                <option value="">All</option>
                @foreach ($types as $t)
                    <option value="{{ $t->value }}" @selected($filters['type'] === $t->value)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-ink px-3 py-1 font-semibold text-paper hover:bg-accent">Filter</button>
    </form>

    <section class="mt-4" aria-label="Articles">
        @forelse ($articles as $item)
            <article class="border-b border-rule py-4">
                <div class="flex flex-wrap items-center gap-2">
                    <x-status-badge :status="$item->status" />
                    <span class="font-mono text-xs uppercase text-ink-soft">{{ $item->type->label() }} · {{ $item->category->name }}</span>
                    @if ($item->is_featured)
                        <span class="font-mono text-xs uppercase text-accent">★ Featured</span>
                    @endif
                </div>

                <h2 class="mt-1 font-serif text-xl font-bold">
                    @if ($trash)
                        {{ $item->title }}
                    @else
                        <a href="{{ route('admin.articles.edit', $item) }}" class="hover:underline">{{ $item->title }}</a>
                    @endif
                </h2>
                <p class="text-xs text-ink-soft">By {{ $item->author->name }} · updated {{ $item->updated_at->diffForHumans() }}</p>

                <div class="mt-3 flex flex-wrap items-center gap-4 text-sm">
                    @if ($trash)
                        <form method="POST" action="{{ route('admin.articles.restore', $item) }}">
                            @csrf
                            <button type="submit" class="font-semibold underline">Restore</button>
                        </form>
                    @else
                        <a href="{{ route('admin.articles.edit', $item) }}" class="underline">Edit</a>

                        @if ($item->status !== \App\Enums\ArticleStatus::Draft)
                            <a href="{{ route('admin.review.show', $item) }}" class="underline">Review page</a>
                        @endif

                        @if ($item->status === \App\Enums\ArticleStatus::Published)
                            <a href="{{ route('articles.show', $item->slug) }}" class="underline">View live</a>
                            <form method="POST" action="{{ route('admin.articles.feature', $item) }}">
                                @csrf
                                <button type="submit" class="underline">{{ $item->is_featured ? 'Remove from featured' : 'Make featured' }}</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('admin.articles.destroy', $item) }}"
                              onsubmit="return confirm('Move this article to the trash? It will disappear from the public site.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-accent underline">Delete</button>
                        </form>
                    @endif
                </div>
            </article>
        @empty
            <p class="py-8 text-ink-soft">{{ $trash ? 'The trash is empty.' : 'No articles match.' }}</p>
        @endforelse

        <div class="mt-6">{{ $articles->links() }}</div>
    </section>
@endsection