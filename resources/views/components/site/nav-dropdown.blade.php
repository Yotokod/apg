@props(['label', 'icon', 'items', 'active' => false])
<div x-data="{ open: false }" class="relative" @mouseenter="if (window.matchMedia('(hover: hover)').matches) open = true" @mouseleave="open = false"
     @keydown.escape="open = false" @click.outside="open = false">
    <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true"
            class="flex min-h-11 items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-medium transition-colors hover:bg-stone-100 dark:hover:bg-stone-800 {{ $active ? 'text-brand-700 dark:text-brand-500' : '' }}">
        <i class="fa-solid {{ $icon }}" aria-hidden="true"></i>{{ $label }}<i class="fa-solid fa-chevron-down text-[10px] transition-transform" :class="open && 'rotate-180'" aria-hidden="true"></i>
    </button>
    <div x-show="open" x-cloak x-transition.origin.top.opacity.duration.150ms class="absolute left-0 top-full z-50 pt-2">
        <ul class="card w-64 space-y-0.5 p-2 shadow-lg">
            @foreach ($items as [$url, $text, $ic])
                <li><a href="{{ $url }}" wire:navigate @click="open = false" class="flex min-h-11 items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium transition-colors hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-stone-800">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-stone-800" aria-hidden="true"><i class="fa-solid {{ $ic }}"></i></span><span class="truncate">{{ $text }}</span></a></li>
            @endforeach
        </ul>
    </div>
</div>
