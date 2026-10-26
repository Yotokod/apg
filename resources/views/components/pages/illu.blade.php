{{-- Animated illustration when available, Font Awesome icon block otherwise --}}
@props(['name', 'icon' => 'fa-leaf'])
@if (view()->exists('components.illustrations.'.$name))
    <x-dynamic-component :component="'illustrations.'.$name" {{ $attributes->class('h-auto w-full') }} />
@else
    <div {{ $attributes->class('grid aspect-[4/3] w-full place-items-center rounded-2xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500') }} aria-hidden="true"><i class="fa-solid {{ $icon }} text-6xl"></i></div>
@endif
