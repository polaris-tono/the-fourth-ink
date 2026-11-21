@extends('layouts.app')

@section('title', $article->exists ? 'Edit article' : 'New article')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('contributor.dashboard') }}" class="text-sm underline">← Back to my articles</a>

        <p class="mt-4 font-mono text-xs uppercase tracking-widest text-block">Writers' Block</p>
        <h1 class="font-mono text-3xl uppercase text-block">{{ $article->exists ? 'Edit article' : 'New article' }}</h1>

        @if ($article->exists && $article->status === \App\Enums\ArticleStatus::RevisionRequested && $article->reviews->first())
            <div class="mt-4 border-l-4 border-orange-600 bg-orange-50 px-4 py-3 text-sm">
                <strong>Editor's feedback:</strong> {{ $article->reviews->first()->comment }}
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data"
              action="{{ $article->exists ? route('contributor.articles.update', $article) : route('contributor.articles.store') }}"
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
                        <option value="">Choose…</option>
                        @foreach ($types as $t)
                            <option value="{{ $t->value }}" @selected(old('type', $article->type?->value) === $t->value)>{{ $t->label() }}</option>
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
                    Markdown is supported: <code>## Heading</code>, <code>**bold**</code>, <code>*italic*</code>, <code>- list item</code>. HTML is not allowed.
                </p>
                @error('body')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="cover" class="block text-sm font-semibold">Cover image <span class="font-normal text-ink-soft">(optional · JPG, PNG or WebP · up to 2 MB · only images you have the right to use)</span></label>

                @if ($article->exists && $article->coverUrl())
                    <img src="{{ $article->coverUrl() }}" alt="" class="mt-2 h-40 w-auto rounded border border-rule object-cover">
                    <label class="mt-2 flex items-center gap-2 text-sm">
                        <input type="checkbox" name="remove_cover" value="1"> Remove the current cover image
                    </label>
                @endif

                <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp"
                       class="mt-2 block w-full text-sm">
                @error('cover')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror

                <label for="cover_alt" class="mt-3 block text-sm font-semibold">Describe the image <span class="font-normal text-ink-soft">(for screen readers, up to 200 characters)</span></label>
                <input id="cover_alt" name="cover_alt" type="text" maxlength="200"
                       value="{{ old('cover_alt', $article->cover_alt) }}"
                       class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                @error('cover_alt')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            @error('action')<p class="text-sm text-accent">{{ $message }}</p>@enderror

            <div class="flex flex-wrap items-center gap-3 border-t border-rule pt-4">
                <button type="submit" name="action" value="save_draft"
                        class="border-2 border-block px-4 py-2 font-semibold text-block hover:bg-block-soft">Save as draft</button>
                <button type="submit" name="action" value="submit"
                        class="bg-block px-4 py-2 font-semibold text-paper hover:bg-ink">Submit for review</button>
                <span class="text-xs text-ink-soft">After you submit, you can't edit until an editor responds.</span>
            </div>
        </form>
    </div>
@endsection