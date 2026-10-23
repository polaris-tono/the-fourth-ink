@extends('layouts.app')

@section('title', $article->title)

@section('content')
    @php $isBlock = $article->type !== \App\Enums\ArticleType::News; @endphp

    <article class="mx-auto max-w-3xl">
        <header>
            <p class="text-xs font-semibold uppercase tracking-widest {{ $isBlock ? 'font-mono text-block' : 'text-accent' }}">
                <a href="{{ route('categories.show', $article->category) }}" class="hover:underline">{{ $article->category->name }}</a>
                ·
                @if ($isBlock)
                    <a href="{{ route('writers-block', ['type' => $article->type->value]) }}" class="hover:underline">{{ $article->type->label() }}</a>
                @else
                    {{ $article->type->label() }}
                @endif
            </p>

            <h1 class="mt-2 font-serif text-4xl font-black leading-tight sm:text-5xl">{{ $article->title }}</h1>

            @if ($article->excerpt)
                <p class="mt-3 text-xl text-ink-soft">{{ $article->excerpt }}</p>
            @endif

            <p class="mt-4 border-y border-rule py-2 text-sm text-ink-soft">
                By <strong class="text-ink">{{ $article->author->name }}</strong>
                · {{ $article->published_at->format('F j, Y') }}
                · {{ number_format($article->views) }} {{ Str::plural('view', $article->views) }}
            </p>
        </header>

        {{-- bodyHtml() strips raw HTML and unsafe links, so this output is safe --}}
        <div class="article-body mt-6">{!! $article->bodyHtml() !!}</div>
    </article>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-12 max-w-3xl" aria-labelledby="related-heading">
            <h2 id="related-heading" class="border-b-2 border-ink pb-1 font-serif text-2xl font-bold">
                More in {{ $article->category->name }}
            </h2>
            @foreach ($related as $story)
                <x-article-card :article="$story" />
            @endforeach
        </section>
    @endif
@endsection