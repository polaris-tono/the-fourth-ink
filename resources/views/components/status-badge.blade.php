@props(['status'])

@php
    $classes = match ($status) {
        \App\Enums\ArticleStatus::Draft => 'border-gray-500 bg-gray-100 text-gray-800',
        \App\Enums\ArticleStatus::PendingReview => 'border-amber-600 bg-amber-50 text-amber-900',
        \App\Enums\ArticleStatus::RevisionRequested => 'border-orange-600 bg-orange-50 text-orange-900',
        \App\Enums\ArticleStatus::Approved => 'border-blue-600 bg-blue-50 text-blue-900',
        \App\Enums\ArticleStatus::Published => 'border-green-700 bg-green-50 text-green-900',
        \App\Enums\ArticleStatus::Rejected => 'border-red-700 bg-red-50 text-red-900',
    };
@endphp

<span class="inline-block border px-2 py-0.5 font-mono text-xs uppercase tracking-wide {{ $classes }}">{{ $status->label() }}</span>