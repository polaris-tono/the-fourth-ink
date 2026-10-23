@extends('layouts.app')

@section('title', 'Edit category')

@section('content')
    <div class="mx-auto max-w-xl">
        <a href="{{ route('admin.categories.index') }}" class="text-sm underline">← Back to categories</a>
        <h1 class="mt-3 font-serif text-4xl font-black">Edit category</h1>
        <p class="mt-1 text-sm text-ink-soft">Web address: <code>/category/{{ $category->slug }}</code> (this stays the same, so old links keep working).</p>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-semibold">Name</label>
                <input id="name" name="name" type="text" required maxlength="100" value="{{ old('name', $category->name) }}"
                       class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">
                @error('name')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-semibold">Description</label>
                <textarea id="description" name="description" rows="3" maxlength="500"
                          class="mt-1 w-full border border-ink/40 bg-white px-3 py-2">{{ old('description', $category->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sort_order" class="block text-sm font-semibold">Menu position</label>
                <input id="sort_order" name="sort_order" type="number" required min="0" max="999"
                       value="{{ old('sort_order', $category->sort_order) }}"
                       class="mt-1 w-28 border border-ink/40 bg-white px-3 py-2">
                @error('sort_order')<p class="mt-1 text-sm text-accent">{{ $message }}</p>@enderror
            </div>

            <button type="submit" class="bg-ink px-4 py-2 font-semibold text-paper hover:bg-accent">Save changes</button>
        </form>
    </div>
@endsection