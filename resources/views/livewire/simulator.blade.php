<div>
    <x-page-hero :title="__('sim.ui.title')" :subtitle="__('sim.ui.subtitle')" icon="fa-chart-line" />

    {{-- Client-side player (resources/js/sim/player.js); Livewire only injects the config. --}}
    <div class="sim mx-auto max-w-7xl px-4 pb-6 pt-4" x-data="simulator(@js($config))" wire:ignore>
        <p x-show="resumed" x-cloak class="mb-3 flex items-center gap-2 rounded-xl bg-brand-50 px-4 py-2 text-sm font-medium text-brand-800 dark:bg-brand-800/30 dark:text-brand-100" role="status">
            <i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i><span>{{ __('sim.ui.resumed') }}</span>
        </p>

        <x-sim.timeline />
        <x-sim.hud />

        <div class="grid gap-4 lg:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)]" x-ref="stage" style="scroll-margin-top: 6rem">
            <figure class="card relative self-start overflow-hidden p-0 lg:sticky lg:top-24" role="img" :aria-label="t('ui.scene_label', { title: node.title })">
                <div class="sim-stage mx-auto" x-ref="scene"><x-sim.scene /></div>
                <span class="absolute left-2 top-2 max-w-[60%] truncate rounded-full bg-ink-900/85 px-3 py-1 text-xs font-bold text-white" x-text="chapterName"></span>
                <span x-show="sceneChip" class="absolute right-2 top-2 max-w-[45%] truncate rounded-full bg-white/90 px-3 py-1 text-sm font-extrabold text-brand-700 shadow" x-text="sceneChip"></span>
                <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-ink-900/85 to-transparent px-3 pb-2 pt-6 text-white">
                    <span class="block truncate text-sm font-semibold sm:text-base" x-text="node.title"></span>
                </figcaption>
            </figure>

            <section class="min-w-0 space-y-4" aria-labelledby="sim-node-title">
                <div class="card p-4 sm:p-5">
                    <p class="truncate text-xs font-bold uppercase tracking-wide text-brand-700 dark:text-brand-500" x-text="t('ui.chapter', { n: chapter, total: chapterCount }) + ' · ' + chapterName"></p>
                    <h2 id="sim-node-title" x-ref="title" tabindex="-1" class="mt-1 text-xl font-extrabold outline-none sm:text-2xl" x-text="node.title"></h2>
                    <p class="mt-2 text-base leading-relaxed text-stone-700 dark:text-stone-300" aria-live="polite" x-text="node.body"></p>
                </div>

                <div x-show="error" x-cloak class="flex items-start gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950/40 dark:text-red-200" role="alert">
                    <i class="fa-solid fa-circle-exclamation mt-1" aria-hidden="true"></i>
                    <p><strong class="block">{{ __('sim.ui.blocked') }}</strong><span x-text="errorText"></span></p>
                </div>

                <x-sim.panel />

                <div x-show="!isEnd" class="space-y-2">
                    <h3 id="sim-choices-label" class="text-sm font-bold uppercase tracking-wide text-stone-600 dark:text-stone-400">{{ __('sim.ui.choices') }}</h3>
                    <div role="radiogroup" aria-labelledby="sim-choices-label" aria-describedby="sim-choices-hint" x-ref="choices" class="grid gap-3">
                        <template x-for="(c, i) in choices" :key="run.node + c.code">
                            <button type="button" role="radio" aria-checked="false" data-choice :tabindex="i === 0 ? 0 : -1" :disabled="!!error"
                                    @click="pick(c)" @keydown="chooseKey($event, i)"
                                    class="sim-choice card flex min-h-16 w-full items-start gap-3 border-2 p-3 text-left disabled:cursor-not-allowed disabled:opacity-50 sm:p-4">
                                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-brand-50 text-xl text-brand-700 dark:bg-brand-800/30 dark:text-brand-100"><i class="fa-solid" :class="c.icon || 'fa-circle-dot'" aria-hidden="true"></i></span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-base font-bold" x-text="c.label"></span>
                                    <span class="mt-0.5 block text-sm text-stone-600 dark:text-stone-400" x-text="c.hint"></span>
                                </span>
                                <i class="fa-solid fa-chevron-right mt-4 shrink-0 text-stone-400" aria-hidden="true"></i>
                            </button>
                        </template>
                    </div>
                    <p id="sim-choices-hint" class="text-sm text-stone-500 dark:text-stone-400">{{ __('sim.ui.choose_hint') }}</p>
                </div>

                <x-sim.ending />
            </section>
        </div>

        <template x-if="isEnd && r">
            <x-sim.report />
        </template>

        <x-sim.actions />
        <x-sim.dialogs />
    </div>
</div>
