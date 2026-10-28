{{-- Final report (screen + print via @media print in sim.css). Rendered only when the run reached the ending. --}}
@php $th = 'px-3 py-2 text-left font-semibold'; $td = 'px-3 py-2 tabular-nums'; @endphp
<article class="sim-report card mt-6 space-y-6 p-4 sm:p-6" aria-labelledby="sim-report-title">
    <header class="flex flex-wrap items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="sim-print-only text-sm font-bold">{{ site('name') }}</p>
            <h2 id="sim-report-title" class="text-2xl font-extrabold">{{ __('sim.report.title') }}</h2>
            <p class="text-sm text-stone-600 dark:text-stone-400">
                <span x-text="t('ui.version', { v: cfg.version })"></span> · <span x-text="t('ui.date', { d: new Date(cfg.date).toLocaleDateString(cfg.locale === 'en' ? 'en-GB' : 'fr-FR') })"></span>
                · <span x-text="t('endings.' + ending + '.title')"></span>
            </p>
        </div>
        <button type="button" class="btn-primary no-print" @click="print()"><i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('sim.ui.print') }}</button>
    </header>
    <p class="rounded-xl border-2 border-amber-300 bg-amber-50 p-3 text-sm font-semibold text-amber-900 dark:border-amber-700 dark:bg-amber-950/30 dark:text-amber-100" role="note">
        <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> {{ __('sim.ui.disclaimer') }}
    </p>

    {{-- Scenario comparison: bar chart + table alternative --}}
    <section aria-labelledby="sim-compare-title" x-show="compare">
        <h3 id="sim-compare-title" class="text-lg font-extrabold">{{ __('sim.compare.title') }}</h3>
        <p class="mb-3 text-sm text-stone-600 dark:text-stone-400">{{ __('sim.compare.intro') }}</p>
        <div class="space-y-2" role="img" aria-label="{{ __('sim.compare.chart') }}" aria-describedby="sim-compare-table">
            <template x-for="s in scenarios" :key="s">
                <div class="grid grid-cols-[6.5rem_minmax(0,1fr)] items-center gap-2 text-sm sm:grid-cols-[9rem_minmax(0,1fr)]">
                    <span class="truncate font-semibold" :class="s === d.scenario && 'text-brand-700 dark:text-brand-500'" x-text="t('scenarios.' + s)"></span>
                    <div class="relative h-8 rounded-lg bg-stone-100 dark:bg-stone-800">
                        <div class="absolute inset-y-0 left-0 rounded-lg transition-all duration-700"
                             :class="compare && compare[s].result < 0 ? 'bg-red-500' : (s === d.scenario ? 'bg-brand-600' : 'bg-ink-800 dark:bg-stone-500')"
                             :style="compare && `width: ${Math.max(2, Math.abs(compare[s].result) / compareMax * 100)}%`"></div>
                        <span class="absolute inset-y-0 left-2 flex items-center truncate font-bold text-white mix-blend-difference" x-text="compare && money(compare[s].result)"></span>
                    </div>
                </div>
            </template>
        </div>
        <div class="mt-3 overflow-x-auto">
            <table id="sim-compare-table" class="w-full min-w-[30rem] text-sm">
                <thead class="bg-stone-100 dark:bg-stone-800"><tr>
                    <th scope="col" class="{{ $th }}">{{ __('sim.compare.scenario') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.hud.mortality') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.results.revenue') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.results.opex') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.results.result') }}</th>
                </tr></thead>
                <tbody>
                    <template x-for="s in scenarios" :key="s">
                        <tr class="border-t border-stone-200 dark:border-stone-800" :class="s === d.scenario && 'font-bold'">
                            <th scope="row" class="{{ $th }}" x-text="t('scenarios.' + s)"></th>
                            <td class="{{ $td }}" x-text="compare && pct(compare[s].mortalityPct)"></td>
                            <td class="{{ $td }}" x-text="compare && money(compare[s].revenue)"></td>
                            <td class="{{ $td }}" x-text="compare && money(compare[s].opex)"></td>
                            <td class="{{ $td }}" x-text="compare && money(compare[s].result)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section aria-labelledby="sim-r-inputs">
            <h3 id="sim-r-inputs" class="mb-2 text-lg font-extrabold">{{ __('sim.report.inputs') }}</h3>
            <dl class="divide-y divide-stone-200 text-sm dark:divide-stone-800">
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.goal') }}</dt><dd class="text-right font-semibold" x-text="t('goals.' + d.goal)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.flock') }}</dt><dd class="text-right font-semibold tabular-nums" x-text="fmt(d.flock)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5" x-show="d.goal === 'repro'"><dt>{{ __('sim.goals.repro') }}</dt><dd class="text-right font-semibold" x-text="t('controls.split', { m: fmt(r.males), f: fmt(r.females), r: fmt(params.male_ratio) })"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.zone') }}</dt><dd class="text-right font-semibold" x-text="t('zones.' + d.zone) + ' · ' + t('controls.per_chick', { price: money(chickPrice(d.zone)) })"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.scenario') }}</dt><dd class="text-right font-semibold" x-text="t('scenarios.' + d.scenario)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.duration') }}</dt><dd class="text-right font-semibold" x-text="t('report.days', { d: fmt(r.days) })"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.alive_end') }}</dt><dd class="text-right font-semibold tabular-nums" x-text="fmt(r.aliveEnd) + ' (' + pct(r.mortalityPct) + ')'"></dd></div>
            </dl>
            <h4 class="mb-1 mt-4 font-bold">{{ __('sim.report.path') }}</h4>
            <ol class="list-decimal space-y-1 pl-5 text-sm">
                <template x-for="(p, i) in pathLabels" :key="i">
                    <li><span class="text-stone-500" x-text="p.title + ' : '"></span><span class="font-semibold" :class="p.bad && 'text-red-700 dark:text-red-400'" x-text="p.label"></span></li>
                </template>
            </ol>
        </section>

        <section aria-labelledby="sim-r-budget">
            <h3 id="sim-r-budget" class="mb-2 text-lg font-extrabold">{{ __('sim.report.budget') }}</h3>
            <dl class="divide-y divide-stone-200 text-sm dark:divide-stone-800">
                <template x-for="l in costLines" :key="l.key">
                    <div class="flex justify-between gap-3 py-1.5"><dt x-text="l.label"></dt><dd class="text-right tabular-nums" x-text="money(l.value)"></dd></div>
                </template>
                <div class="flex justify-between gap-3 py-1.5 font-bold"><dt>{{ __('sim.results.opex') }}</dt><dd class="text-right tabular-nums" x-text="money(r.opex)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5 font-bold"><dt>{{ __('sim.results.revenue') }}</dt><dd class="text-right tabular-nums" x-text="money(r.revenue)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.results.margin') }}</dt><dd class="text-right tabular-nums" x-text="money(r.margin)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.results.amortization') }}</dt><dd class="text-right tabular-nums" x-text="money(r.amortization)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5 text-base font-extrabold"><dt>{{ __('sim.results.result') }}</dt><dd class="text-right tabular-nums" :class="r.result < 0 ? 'text-red-600' : 'text-green-700 dark:text-green-400'" x-text="money(r.result)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.results.break_even') }}</dt><dd class="text-right tabular-nums" x-text="r.breakEvenRevenue === null ? t('results.not_reached') : money(r.breakEvenRevenue)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt x-text="d.goal === 'chair' ? t('results.unit_cost_chair') : t('results.unit_cost_eggs')"></dt><dd class="text-right tabular-nums" x-text="money(d.goal === 'chair' ? r.unitCost : (r.opex + r.amortization - r.reformRevenue) / Math.max(1, r.eggsNet))"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.results.cost_per_bird') }}</dt><dd class="text-right tabular-nums" x-text="money(r.costPerBird)"></dd></div>
            </dl>
            <p class="mt-2 text-xs text-stone-600 dark:text-stone-400">{{ __('sim.results.excluded') }}</p>
        </section>
    </div>

    <section aria-labelledby="sim-r-feed">
        <h3 id="sim-r-feed" class="mb-2 text-lg font-extrabold">{{ __('sim.report.feed') }}</h3>
        <div class="max-h-96 overflow-auto rounded-xl border border-stone-200 dark:border-stone-800 print:max-h-none">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-stone-100 dark:bg-stone-800"><tr>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.period') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.ration_end') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.kg') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.cumul') }}</th>
                </tr></thead>
                <tbody>
                    <template x-for="p in periods" :key="p.from">
                        <tr class="border-t border-stone-200 dark:border-stone-800">
                            <th scope="row" class="{{ $th }} font-normal" x-text="periodLabel(p)"></th>
                            <td class="{{ $td }}" x-text="fmt(p.grams, 1)"></td>
                            <td class="{{ $td }}" x-text="fmt(p.kg, 1)"></td>
                            <td class="{{ $td }}" x-text="fmt(p.cumul, 1)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-sm font-semibold" x-text="t('report.feed_total', { kg: fmt(r.feedWithWaste, 1), bags: fmt(r.bags), bag: d.bagKg, left: fmt(r.leftoverKg, 1), cost: money(r.lines.feed), price: money(r.feedPriceKg) })"></p>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
        <section aria-labelledby="sim-r-building">
            <h3 id="sim-r-building" class="mb-2 text-lg font-extrabold">{{ __('sim.report.building') }}</h3>
            <ul class="space-y-1 text-sm">
                <li x-text="t('build_modes.' + d.buildMode)"></li>
                <li x-text="t('controls.surface_brooder') + ' : ' + fmt(r.surfaceBrooder, 1) + ' m² (' + t('controls.density', { d: fmt(params.density_brooder) }) + ')'"></li>
                <li x-text="t('controls.surface_adult') + ' : ' + fmt(r.surfaceAdult, 1) + ' m² (' + t('controls.density', { d: fmt(r.density) }) + ')'"></li>
                <li x-show="r.invest > 0" x-text="t('controls.invest') + ' : ' + money(r.invest)"></li>
                <li class="text-stone-600 dark:text-stone-400">{{ __('sim.controls.surface_note') }}</li>
            </ul>
        </section>
        <section aria-labelledby="sim-r-heat">
            <h3 id="sim-r-heat" class="mb-2 text-lg font-extrabold">{{ __('sim.report.heat') }}</h3>
            <ul class="grid grid-cols-2 gap-1 text-sm">
                <template x-for="w in tempWeeks" :key="w.w"><li x-text="t('controls.temp_week', { w: w.w }) + ' : ' + t('controls.temp_range', { min: w.min, max: w.max })"></li></template>
            </ul>
            <p class="mt-2 text-sm">{{ __('sim.report.controls_list') }}</p>
        </section>
    </div>

    <section aria-labelledby="sim-r-growth">
        <h3 id="sim-r-growth" class="mb-2 text-lg font-extrabold" x-text="d.goal === 'chair' ? t('report.growth') : t('report.laying')"></h3>
        <p x-show="d.goal === 'chair'" class="text-sm" x-text="t('report.growth_chair', { w: fmt(r.weightKg, 2), d: fmt(r.days) })"></p>
        <template x-if="d.goal !== 'chair'">
            <dl class="divide-y divide-stone-200 text-sm dark:divide-stone-800">
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.report.eggs_laid') }}</dt><dd class="tabular-nums" x-text="fmt(r.eggsLaidTotal)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt x-text="t('report.eggs_net', { p: fmt(r.lossPct) })"></dt><dd class="tabular-nums" x-text="fmt(r.eggsNet)"></dd></div>
                <div class="flex justify-between gap-3 py-1.5"><dt>{{ __('sim.controls.eggs_female') }}</dt><dd class="tabular-nums" x-text="fmt(r.eggsPerFemale) + ' · ' + t('controls.eggs_target', { t: fmt(params.laying_target_eggs) })"></dd></div>
                <template x-if="d.goal === 'repro'">
                    <div class="space-y-1 py-1.5">
                        <p x-text="t('controls.alloc_table') + ' : ' + fmt(r.eggsTable) + ' · ' + t('controls.alloc_incubate') + ' : ' + fmt(r.eggsIncubated) + ' · ' + t('controls.alloc_sell') + ' : ' + fmt(r.eggsSold)"></p>
                        <p class="font-semibold" x-text="hatchFormula"></p>
                    </div>
                </template>
            </dl>
        </template>
        <p x-show="d.goal !== 'chair'" class="mt-2 text-sm" x-text="t('report.growth_breeder', { w: fmt(params.breeder_max_weight_kg) })"></p>
    </section>

    <section aria-labelledby="sim-r-risks">
        <h3 id="sim-r-risks" class="mb-2 text-lg font-extrabold">{{ __('sim.report.risks') }}</h3>
        <ul class="space-y-1 text-sm">
            <template x-for="(risk, i) in risks" :key="i"><li class="flex gap-2"><i class="fa-solid fa-triangle-exclamation mt-1 text-amber-600" aria-hidden="true"></i><span x-text="risk"></span></li></template>
            <li class="flex gap-2"><i class="fa-solid fa-user-doctor mt-1 text-brand-600" aria-hidden="true"></i><span>{{ __('sim.bad.vet') }}</span></li>
        </ul>
    </section>

    <section aria-labelledby="sim-r-hyp">
        <h3 id="sim-r-hyp" class="mb-2 text-lg font-extrabold">{{ __('sim.report.hypotheses') }}</h3>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[30rem] text-sm">
                <thead class="bg-stone-100 dark:bg-stone-800"><tr>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.param') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.value') }}</th>
                    <th scope="col" class="{{ $th }}">{{ __('sim.report.status') }}</th>
                </tr></thead>
                <tbody>
                    <template x-for="m in cfg.meta" :key="m.key">
                        <tr class="border-t border-stone-200 dark:border-stone-800">
                            <th scope="row" class="{{ $th }} font-normal" x-text="m.label"></th>
                            <td class="{{ $td }} whitespace-nowrap" x-text="paramValue(m) + (isModified(m.key) ? ' *' : '')"></td>
                            <td class="px-3 py-2"><span class="sim-badge" :class="'sim-badge-' + m.status" x-text="t('status.' + m.status)"></span></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="mt-2 text-xs text-stone-600 dark:text-stone-400">* {{ __('sim.ui.modified') }} · {{ __('sim.status.apg') }} : {{ __('sim.status.apg_desc') }} · {{ __('sim.status.generique') }} : {{ __('sim.status.generique_desc') }} · {{ __('sim.status.hypothese') }} : {{ __('sim.status.hypothese_desc') }}</p>
    </section>

    <footer class="border-t border-stone-200 pt-3 text-xs text-stone-600 dark:border-stone-800 dark:text-stone-400">
        <p x-text="t('report.footer', { v: cfg.version, d: cfg.date })"></p>
        <p>{{ __('sim.ui.disclaimer') }}</p>
    </footer>
</article>
