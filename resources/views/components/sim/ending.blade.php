{{-- Ending card (chapter 8): outcome depends on the path taken (success / tight / loss / disaster). --}}
<div x-show="isEnd" x-cloak class="card overflow-hidden p-0" aria-live="polite">
    <div class="p-5 text-center"
         :class="{ 'bg-green-50 dark:bg-green-950/30': ending === 'success', 'bg-amber-50 dark:bg-amber-950/30': ending === 'tight', 'bg-orange-50 dark:bg-orange-950/30': ending === 'loss', 'bg-red-50 dark:bg-red-950/40': ending === 'disaster' }">
        <span class="mx-auto grid h-20 w-20 place-items-center rounded-full text-4xl text-white shadow-lg sim-celebrate"
              :class="{ 'bg-green-600': ending === 'success', 'bg-amber-500': ending === 'tight', 'bg-brand-600': ending === 'loss', 'bg-red-600': ending === 'disaster' }">
            <i class="fa-solid" :class="endingIcon" aria-hidden="true"></i>
        </span>
        <h3 class="mt-3 text-2xl font-extrabold" x-text="t('endings.' + ending + '.title')"></h3>
        <p class="mt-1 text-stone-700 dark:text-stone-300" x-text="t('endings.' + ending + '.text')"></p>
    </div>
    <dl class="grid grid-cols-2 gap-px bg-stone-200 dark:bg-stone-800" x-show="r">
        <div class="bg-white p-3 dark:bg-stone-900"><dt class="truncate text-xs text-stone-500">{{ __('sim.results.revenue') }}</dt><dd class="truncate font-extrabold tabular-nums" x-text="r && money(r.revenue)"></dd></div>
        <div class="bg-white p-3 dark:bg-stone-900"><dt class="truncate text-xs text-stone-500">{{ __('sim.results.opex') }}</dt><dd class="truncate font-extrabold tabular-nums" x-text="r && money(r.opex)"></dd></div>
        <div class="bg-white p-3 dark:bg-stone-900"><dt class="truncate text-xs text-stone-500">{{ __('sim.results.result') }}</dt><dd class="truncate font-extrabold tabular-nums" :class="r && r.result < 0 ? 'text-red-600' : 'text-green-700 dark:text-green-400'" x-text="r && money(r.result)"></dd></div>
        <div class="bg-white p-3 dark:bg-stone-900"><dt class="truncate text-xs text-stone-500">{{ __('sim.results.margin_rate') }}</dt><dd class="truncate font-extrabold tabular-nums" x-text="marginRate === null ? '—' : pct(marginRate)"></dd></div>
    </dl>
    <div class="flex flex-wrap gap-2 p-4">
        <button type="button" class="btn-primary flex-1" @click="print()"><i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('sim.ui.print') }}</button>
        <a href="{{ route('courses') }}" wire:navigate class="btn-ghost flex-1"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> {{ __('sim.ui.learn') }}</a>
        <a href="{{ route('shop') }}" wire:navigate class="btn-accent flex-1"><i class="fa-solid fa-dove" aria-hidden="true"></i> {{ __('sim.ui.shop') }}</a>
    </div>
    <p class="border-t border-stone-200 px-4 py-3 text-sm text-stone-600 dark:border-stone-800 dark:text-stone-400"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> {{ __('sim.ui.disclaimer') }}</p>
</div>
