{{-- Bad-decision pop-up --}}
<div x-show="pending" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center bg-ink-900/60 p-3 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="sim-bad-title"
     @keydown.escape.window="pending = null">
    <div class="card w-full max-w-lg overflow-hidden p-0" @click.outside="pending = null" x-show="pending" style="animation: sim-pop .35s cubic-bezier(.3, 1.4, .5, 1) both">
        <div class="flex items-center gap-3 bg-gradient-to-r from-red-600 to-brand-600 p-4 text-white">
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-white/20 text-2xl motion-safe:animate-pulse"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
            <div class="min-w-0">
                <h2 id="sim-bad-title" class="text-lg font-extrabold">{{ __('sim.bad.title') }}</h2>
                <p class="truncate text-sm text-white/90" x-text="pending?.label"></p>
            </div>
        </div>
        <div class="max-h-[50vh] space-y-3 overflow-y-auto p-4">
            <div>
                <h3 class="flex items-center gap-2 font-bold"><i class="fa-solid fa-lightbulb text-amber-500" aria-hidden="true"></i>{{ __('sim.bad.tip') }}</h3>
                <p class="mt-1 text-base leading-relaxed" x-text="pending?.tip"></p>
            </div>
            <p x-show="chapter === 5" class="rounded-xl bg-stone-100 p-3 text-sm dark:bg-stone-800"><i class="fa-solid fa-user-doctor text-brand-600" aria-hidden="true"></i> {{ __('sim.bad.vet') }}</p>
        </div>
        <div class="grid gap-2 border-t border-stone-200 p-4 sm:grid-cols-2 dark:border-stone-800">
            <button type="button" x-ref="badFirst" class="btn-primary sm:col-span-2" @click="pending = null"><i class="fa-solid fa-hand-pointer" aria-hidden="true"></i> {{ __('sim.bad.choose_again') }}</button>
            <button type="button" class="btn-ghost" @click="goBack()" :disabled="!canBack"><i class="fa-solid fa-backward-step" aria-hidden="true"></i> {{ __('sim.bad.rewind') }}</button>
            <button type="button" class="btn-ghost text-red-700 dark:text-red-400" @click="commit(pending)">{{ __('sim.bad.continue') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
        </div>
    </div>
</div>

{{-- Bottom sheet: advanced settings (editable) or assumptions (read-only) --}}
<div x-show="sheet" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center bg-ink-900/60" role="dialog" aria-modal="true" aria-labelledby="sim-sheet-title"
     @keydown.escape.window="closeSheet()">
    <div class="card flex max-h-[88vh] w-full max-w-3xl flex-col rounded-b-none p-0" @click.outside="closeSheet()" x-show="sheet" style="animation: sim-sheet-up .3s ease-out both">
        <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-stone-300 dark:bg-stone-700" aria-hidden="true"></div>
        <div class="flex items-center justify-between gap-2 border-b border-stone-200 px-4 py-3 dark:border-stone-800">
            <h2 id="sim-sheet-title" class="truncate text-lg font-extrabold" x-text="sheet === 'params' ? t('ui.settings') : t('ui.hypotheses')"></h2>
            <div class="flex shrink-0 gap-2">
                <button type="button" x-show="sheet === 'params'" class="btn-ghost !px-3" @click="resetParams()" :disabled="!Object.keys(overrides).length"><i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i><span class="max-w-24 truncate sm:max-w-none">{{ __('sim.ui.reset_params') }}</span></button>
                <button type="button" x-ref="sheetClose" class="btn-ghost !px-3" @click="closeSheet()" aria-label="{{ __('sim.ui.close') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
        </div>
        <div class="space-y-4 overflow-y-auto px-4 py-3">
            <p class="text-sm text-stone-600 dark:text-stone-400" x-text="sheet === 'params' ? t('ui.params_intro') : t('ui.hyp_intro')"></p>
            <ul class="flex flex-wrap gap-2 text-xs">
                @foreach (['apg', 'generique', 'hypothese'] as $s)
                    <li class="flex items-center gap-1"><span class="sim-badge sim-badge-{{ $s }}">{{ __('sim.status.'.$s) }}</span><span>{{ __('sim.status.'.$s.'_desc') }}</span></li>
                @endforeach
            </ul>
            <template x-for="g in groups" :key="g.key">
                <section>
                    <h3 class="mb-2 text-sm font-extrabold uppercase tracking-wide text-brand-700 dark:text-brand-500" x-text="t('groups.' + g.key)"></h3>
                    <ul class="divide-y divide-stone-200 rounded-xl border border-stone-200 dark:divide-stone-800 dark:border-stone-800">
                        <template x-for="m in g.items" :key="m.key">
                            <li class="grid gap-2 p-3 sm:grid-cols-[minmax(0,1fr)_12rem] sm:items-center">
                                <div class="min-w-0">
                                    <p class="flex flex-wrap items-center gap-2">
                                        <label class="font-semibold" :for="'sim-p-' + m.key" x-text="m.label"></label>
                                        <span class="sim-badge" :class="'sim-badge-' + m.status" x-text="t('status.' + m.status)"></span>
                                        <span x-show="isModified(m.key)" class="sim-badge bg-brand-600 text-white" x-text="t('ui.modified')"></span>
                                    </p>
                                    <p class="text-sm text-stone-600 dark:text-stone-400" x-text="m.note"></p>
                                </div>
                                <div x-show="sheet === 'params'" class="flex items-center gap-2">
                                    <input type="number" step="any" min="0" inputmode="decimal" class="input text-right font-semibold" :id="'sim-p-' + m.key" :value="params[m.key]" @change="setParam(m.key, $event.target.value)">
                                    <span class="w-20 shrink-0 truncate text-sm text-stone-600 dark:text-stone-400" x-text="unit(m.unit)"></span>
                                </div>
                                <p x-show="sheet !== 'params'" class="font-semibold tabular-nums sm:text-right" x-text="paramValue(m)"></p>
                            </li>
                        </template>
                    </ul>
                </section>
            </template>
            <p class="pb-2 text-sm font-semibold text-amber-800 dark:text-amber-200">{{ __('sim.ui.disclaimer') }}</p>
        </div>
    </div>
</div>
