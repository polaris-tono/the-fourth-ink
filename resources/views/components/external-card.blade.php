@props(['item'])

<article class="border-t border-rule py-4">
    @if ($item->image_url && str_starts_with($item->image_url, 'http'))
        {{-- Decorative copy of the headline link, so keyboard and screen-reader users skip it. --}}
        <a href="{{ route('external.show', $item) }}" tabindex="-1" aria-hidden="true" class="mb-3 block">
            <img
                src="{{ $item->image_url }}"
                alt=""
                loading="lazy"
                referrerpolicy="no-referrer"
                onerror="this.parentElement.style.display='none'"
                class="h-44 w-full rounded object-cover"
            >
        </a>
    @endif

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