@props(['title', 'sub' => null, 'href' => null, 'cta' => null])
<div {{ $attributes->class('mb-5 flex flex-wrap items-end justify-between gap-2') }}>
    <div class="min-w-0">
        <h2 class="text-xl font-extrabold sm:text-2xl">{{ $title }}</h2>
        @if ($sub)<p class="mt-0.5 text-sm text-stone-600 dark:text-stone-400">{{ $sub }}</p>@endif
    </div>
    @if ($href && $cta)
        <a href="{{ $href }}" wire:navigate class="inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-brand-700 hover:underline dark:text-brand-500">{{ $cta }} <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
    @endif
</div>
