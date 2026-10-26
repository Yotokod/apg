@props(['value', 'label', 'icon' => 'fa-chart-simple'])
<div {{ $attributes->class('card flex items-center gap-3 p-4') }}>
    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
    <div class="min-w-0">
        <p class="text-2xl font-extrabold leading-none"><span data-count="{{ (int) $value }}">{{ number_format((int) $value) }}</span></p>
        <p class="mt-1 truncate text-sm text-stone-600 dark:text-stone-300">{{ $label }}</p>
    </div>
</div>
