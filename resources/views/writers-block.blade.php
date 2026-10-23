@extends('layouts.app')

@section('title', "Writers' Block")

@section('content')
    <header class="border-b-2 border-block pb-4">
        <p class="font-mono text-xs uppercase tracking-widest text-block">The Fourth Ink presents</p>
        <h1 class="font-mono text-4xl font-medium uppercase tracking-wide text-block">Writers' Block</h1>
        <p class="mt-2 max-w-2xl text-ink-soft">
            Opinions, reviews, analysis and features from our contributors, reviewed by our editors before publication.
        </p>

        <nav aria-label="Article types" class="mt-4 flex flex-wrap gap-2 font-mono text-sm uppercase">
            <a href="{{ route('writers-block') }}"
               class="border border-block px-3 py-1 {{ $type === null ? 'bg-block text-paper' : 'text-block hover:bg-block-soft' }}"
               @if ($type === null) aria-current="page" @endif>All</a>
            @foreach ($types as $t)
                <a href="{{ route('writers-block', ['type' => $t->value]) }}"
                   class="border border-block px-3 py-1 {{ $type === $t->value ? 'bg-block text-paper' : 'text-block hover:bg-block-soft' }}"
                   @if ($type === $t->value) aria-current="page" @endif>{{ $t->label() }}</a>
            @endforeach
        </nav>
    </header>

    <section class="mt-4" aria-label="Contributor articles">
        @forelse ($articles as $article)
            <x-article-card :article="$article" />
        @empty
            <p class="py-6 text-ink-soft">Nothing published here yet.</p>
        @endforelse

        <div class="mt-6">{{ $articles->links() }}</div>
    </section>
@endsection