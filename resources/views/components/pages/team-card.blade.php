@props(['role', 'text', 'icon' => 'fa-user'])
<div {{ $attributes->class('card p-4 text-center') }}>
    <span class="mx-auto grid size-12 place-items-center rounded-full bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
    <h3 class="mt-2 font-bold">{{ $role }}</h3>
    <p class="text-sm text-stone-600 dark:text-stone-300">{{ $text }}</p>
</div>
