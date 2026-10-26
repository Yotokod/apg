@props(['title', 'text' => null])
<div {{ $attributes->class('mb-4') }}>
    <h2 class="text-xl font-extrabold sm:text-2xl">{{ $title }}</h2>
    @if ($text) <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">{{ $text }}</p> @endif
</div>
