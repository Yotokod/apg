{{-- items: [['q' => ..., 'a' => ...], ...] ; one panel open at a time --}}
@props(['items' => [], 'id' => 'acc'])
<div x-data="{ open: null }" {{ $attributes->class('space-y-2') }}>
    @foreach ($items as $i => $item)
        <div class="card overflow-hidden">
            <h3>
                <button type="button" id="{{ $id }}-h{{ $i }}" class="flex min-h-11 w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm font-semibold sm:text-base"
                        :aria-expanded="open === {{ $i }}" aria-controls="{{ $id }}-p{{ $i }}" @click="open = open === {{ $i }} ? null : {{ $i }}">
                    <span>{{ $item['q'] }}</span>
                    <i class="fa-solid fa-chevron-down shrink-0 text-xs text-brand-700 transition-transform dark:text-brand-500" :class="open === {{ $i }} && 'rotate-180'" aria-hidden="true"></i>
                </button>
            </h3>
            <div id="{{ $id }}-p{{ $i }}" role="region" aria-labelledby="{{ $id }}-h{{ $i }}" x-show="open === {{ $i }}" x-cloak class="px-4 pb-4 text-sm leading-relaxed text-stone-600 dark:text-stone-300">{{ $item['a'] }}</div>
        </div>
    @endforeach
</div>
