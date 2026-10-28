{{-- Action dock: sticky above the floating WhatsApp/chat buttons on phones, inline on desktop. --}}
@php $btn = 'flex min-h-12 min-w-0 flex-col items-center justify-center gap-0.5 rounded-xl px-1 py-1 text-xs font-semibold transition hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-40 sm:flex-row sm:gap-2 sm:px-3 sm:text-sm dark:hover:bg-stone-800'; @endphp
<div class="sticky bottom-20 z-30 ml-14 mt-4 sm:ml-0 lg:static">
    <div class="card grid grid-cols-4 gap-1 p-1.5 shadow-lg lg:shadow-sm">
        <button type="button" class="{{ $btn }}" @click="goBack()" :disabled="!canBack" title="{{ __('sim.ui.back') }}">
            <i class="fa-solid fa-arrow-left text-base" aria-hidden="true"></i><span class="max-w-full truncate">{{ __('sim.dock.back') }}</span>
        </button>
        <button type="button" class="{{ $btn }}" @click="restart()" title="{{ __('sim.ui.restart') }}">
            <i class="fa-solid fa-rotate-right text-base" aria-hidden="true"></i><span class="max-w-full truncate">{{ __('sim.dock.restart') }}</span>
        </button>
        <button type="button" class="{{ $btn }}" @click="openSheet('hypotheses')" aria-haspopup="dialog" title="{{ __('sim.ui.hypotheses') }}">
            <i class="fa-solid fa-book-open text-base" aria-hidden="true"></i><span class="max-w-full truncate">{{ __('sim.dock.hypotheses') }}</span>
        </button>
        <button type="button" class="{{ $btn }} text-brand-700 dark:text-brand-500" @click="openSheet('params')" aria-haspopup="dialog" title="{{ __('sim.ui.settings') }}">
            <i class="fa-solid fa-sliders text-base" aria-hidden="true"></i><span class="max-w-full truncate">{{ __('sim.dock.settings') }}</span>
        </button>
    </div>
</div>
