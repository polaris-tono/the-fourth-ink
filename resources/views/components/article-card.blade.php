@props(['article', 'lead' => false])

@php
    $isBlock = $article->type !== \App\Enums\ArticleType::News;
    $cover = $article->coverUrl();
    $decorative = blank($article->cover_alt);
@endphp

<article class="border-t border-rule py-4">
    @if ($cover)
        <a href="{{ route('articles.show', $article->slug) }}"
           @if ($decorative) tabindex="-1" aria-hidden="true" @endif
           class="mb-3 block overflow-hidden rounded bg-rule/30">
            <img src="{{ $cover }}" alt="{{ $article->cover_alt }}" loading="lazy"
                 onerror="this.parentElement.style.display='none'"
                 class="w-full object-cover {{ $lead ? 'h-72' : 'h-44' }}">
        </a>
    @endif

    <p class="text-xs font-semibold uppercase tracking-widest {{ $isBlock ? 'font-mono text-block' : 'text-accent' }}">
        {{ $article->category->name }} · {{ $article->type->label() }}
    </p>

    <h3 class="mt-1 font-serif font-bold leading-snug {{ $lead ? 'text-3xl sm:text-4xl' : 'text-xl' }}">
        <a href="{{ route('articles.show', $article->slug) }}" class="hover:underline">{{ $article->title }}</a>
    </h3>

    @if ($article->excerpt)
        <p class="mt-2 {{ $lead ? 'text-lg' : '' }} text-ink-soft">{{ $article->excerpt }}</p>
    @endif

    <p class="mt-2 text-xs text-ink-soft">
        By {{ $article->author->name }} · {{ $article->published_at->format('M j, Y') }}
    </p>
</article>