@props(['item'])

<article class="border-t border-rule py-4">
    <p class="text-xs font-semibold uppercase tracking-widest text-ink-soft">
        {{ $item->category->name }} · {{ $item->source_name ?? 'External source' }}
    </p>
    <h3 class="mt-1 font-serif text-lg font-bold leading-snug">
        <a href="{{ route('external.show', $item) }}" class="hover:underline">{{ $item->title }}</a>
    </h3>
    @if ($item->published_at)
        <p class="mt-1 text-xs text-ink-soft">{{ $item->published_at->format('M j, Y') }}</p>
    @endif
</article>