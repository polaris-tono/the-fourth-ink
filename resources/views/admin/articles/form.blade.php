@extends('layouts.app')

@section('title', $article->exists ? 'Edit article' : 'New article')

@section('content')
    @php
        // "Publish now" is offered for new articles and for the admin's own drafts.
        $canPublishNow = ! $article->exists
            || ($article->status === \App\Enums\ArticleStatus::Draft && $article->user_id === auth()->id());
    @endphp

    <div class="mx-auto max-w-3xl">
        <a href="{{ route('admin.articles.index') }}" class="text-sm underline">← Back to articles</a>

        <h1 class="mt-3 font-serif text-4xl font-black">{{ $article->exists ? 'Edit article' : 'New article' }}</h1>

        @if ($article->exists)
            <p class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                <x-status-badge :status="$article->status" />
                @if ($article->status !== \App\Enums\ArticleStatus::Draft)
                    <a href="{{ route('admin.review.show', $article) }}" class="underline">Review page</a>
                @endif
                @if ($article->status === \App\Enums\ArticleStatus::Published)
                    <span class="text-ink-soft">Changes to a published article go live immediately.</span>
                @endif
            </p>
        @else
            <p class="mt-2 text-sm text-ink-soft">
                Use the “News” type for staff-written stories such as Campus news.
            </p>
        @endif

        <form method="POST"
              action="{{ $article->exists ? route('admin.articles.update', $article) : route('admin.articles.store') }}"
              class="mt-6 space-y-5">
            @csrf
            @if ($article->exists) @method('PUT') @endif

            <div>
                <label for="title" class="block text-sm font-semibold">Title</label>
                <input id="title" name="title" type="text" required maxlength="200"
                       value="{{ old('title', $article->title) }}"
                       class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                @error('title')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="type" class="block text-sm font-semibold">Type</label>
                    <select id="type" name="type" required class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" @selected(old('type', $article->type?->value ?? 'news') === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-semibold">Category</label>
                    <select id="category_id" name="category_id" required class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                        <option value="">Choose…</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) old('category_id', $article->category_id) === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="excerpt" class="block text-sm font-semibold">Summary <span class="font-normal text-ink-soft">(optional, up to 300 characters)</span></label>
                <textarea id="excerpt" name="excerpt" rows="2" maxlength="300"
                          class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">{{ old('excerpt', $article->excerpt) }}</textarea>
                @error('excerpt')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="body" class="block text-sm font-semibold">Article</label>
                <textarea id="body" name="body" rows="16" required aria-describedby="body-hint"
                          class="mt-1 w-full border border-ink/40 bg-white px-3 py-2 font-mono text-sm">{{ old('body', $article->body) }}</textarea>
                <p id="body-hint" class="mt-1 text-xs text-ink-soft">
                    Markdown is supported: <code>## Heading</code>, <code>**bold**</code>, <code>- list item</code>. HTML is not allowed.
                </p>
                @error('body')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            @error('action')<p class="text-sm text-accent">{{ $message }}</p>@enderror

            <div class="flex flex-wrap items-center gap-3 border-t border-rule pt-4">
                <button type="submit" name="action" value="save"
                        class="border-2 border-ink px-4 py-2 font-semibold hover:bg-white">
                    {{ $article->exists ? 'Save changes' : 'Save as draft' }}
                </button>
                @if ($canPublishNow)
                    <button type="submit" name="action" value="publish_now"
                            class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Publish now</button>
                @endif
            </div>
        </form>
    </div>
@endsection