@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <header class="border-b-2 border-ink pb-4">
        <p class="font-mono text-xs uppercase tracking-widest text-accent">Editorial desk</p>
        <h1 class="font-serif text-4xl font-black">Categories</h1>
        <p class="mt-1 text-sm text-ink-soft">The main menu is built from this list, in this order.</p>
    </header>

    @error('category')
        <p role="alert" class="mt-4 border border-accent bg-white px-3 py-2 text-sm text-accent">{{ $message }}</p>
    @enderror

    <div class="mt-6 grid gap-10 lg:grid-cols-3">
        <section class="lg:col-span-2" aria-label="Existing categories">
            @foreach ($categories as $c)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-rule py-3">
                    <div>
                        <strong>{{ $c->name }}</strong>
                        <span class="font-mono text-xs text-ink-soft">/{{ $c->slug }} · order {{ $c->sort_order }} · {{ $c->articles_count }} {{ Str::plural('article', $c->articles_count) }}</span>
                        @if ($c->description)<p class="text-sm text-ink-soft">{{ $c->description }}</p>@endif
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <a href="{{ route('admin.categories.edit', $c) }}" class="underline">Edit</a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $c) }}"
                              onsubmit="return confirm('Delete this category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-accent underline">Delete</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </section>

        <section aria-labelledby="new-cat-heading">
            <h2 id="new-cat-heading" class="font-serif text-2xl font-bold">New category</h2>
            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-3 space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-semibold">Name</label>
                    <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name') }}"
                           class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                    @error('name')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="description" class="block text-sm font-semibold">Description <span class="font-normal text-ink-soft">(optional)</span></label>
                    <textarea id="description" name="description" rows="2" maxlength="500"
                              class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="sort_order" class="block text-sm font-semibold">Menu position <span class="font-normal text-ink-soft">(optional)</span></label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order') }}"
                           class="mt-1 w-28 border border-ink/40 bg-white px-3 py-2">
                    @error('sort_order')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Add category</button>
            </form>
        </section>
    </div>
@endsection