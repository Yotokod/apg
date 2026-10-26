{{-- items: [[label, url|null], ...] ; home is prepended --}}
@props(['items' => []])
<nav aria-label="Breadcrumb" class="mx-auto max-w-7xl px-4 pt-3 text-sm text-stone-600 dark:text-stone-300">
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        <li><a href="{{ route('home') }}" wire:navigate class="inline-flex min-h-11 items-center hover:underline">{{ __('pages.common.home') }}</a></li>
        @foreach ($items as [$label, $url])
            <li class="flex min-w-0 items-center gap-2"><i class="fa-solid fa-chevron-right text-[10px]" aria-hidden="true"></i>
                @if ($url) <a href="{{ $url }}" wire:navigate class="inline-flex min-h-11 items-center hover:underline">{{ $label }}</a>
                @else <span class="max-w-[60vw] truncate font-semibold text-ink-900 dark:text-white" aria-current="page">{{ $label }}</span> @endif
            </li>
        @endforeach
    </ol>
</nav>
