{{-- Interactive controls embedded in the story, shown by node kind. Inputs mutate run.decisions; results recompute live. --}}
@php
    $chip = 'flex min-h-11 min-w-0 flex-col items-center justify-center rounded-xl border-2 px-2 py-1.5 text-center text-sm font-semibold transition';
    $chipOn = "'border-brand-600 bg-brand-50 text-brand-800 dark:bg-brand-800/30 dark:text-brand-100'";
    $chipOff = "'border-stone-200 hover:border-brand-500 dark:border-stone-700'";
@endphp
<div x-show="r || error">
    {{-- Chapter 1: flock size, zone, scenario --}}
    <div x-show="node.kind === 'flock'" class="card space-y-4 p-4">
        <div class="flex items-end justify-between gap-2">
            <label for="sim-flock-range" class="font-semibold">{{ __('sim.controls.flock') }}</label>
            <output for="sim-flock-range" class="text-2xl font-extrabold tabular-nums text-brand-700 dark:text-brand-500" x-text="fmt(d.flock)"></output>
        </div>
        <input id="sim-flock-range" type="range" class="sim-range" min="0" :max="snaps.length - 1" step="1" :value="snapIndex"
               @input="setSnap($event.target.value)" :style="`--fill: ${snapIndex / (snaps.length - 1) * 100}%`" :aria-valuetext="fmt(d.flock)">
        <div class="grid grid-cols-3 gap-2 sm:grid-cols-6" role="group" aria-label="{{ __('sim.controls.flock_snap') }}">
            <template x-for="s in snaps" :key="s">
                <button type="button" class="{{ $chip }}" :class="d.flock === s ? {{ $chipOn }} : {{ $chipOff }}" :aria-pressed="d.flock === s" @click="d.flock = s" x-text="fmt(s)"></button>
            </template>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" class="btn-ghost !px-4" @click="stepFlock(-1)" aria-label="{{ __('sim.controls.less') }}"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
            <input type="number" inputmode="numeric" class="input text-center text-lg font-bold" :min="params.min_flock" step="100" :value="d.flock"
                   @change="typeFlock($event.target.value)" aria-label="{{ __('sim.controls.flock') }}">
            <button type="button" class="btn-ghost !px-4" @click="stepFlock(1)" aria-label="{{ __('sim.controls.more') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
        </div>
        <p class="text-sm text-stone-600 dark:text-stone-400"><i class="fa-solid fa-circle-info text-brand-600" aria-hidden="true"></i> <span x-text="t('controls.flock_min', { min: fmt(params.min_flock) })"></span></p>
        <p x-show="d.goal === 'repro' && r" class="rounded-xl bg-stone-100 px-3 py-2 text-sm dark:bg-stone-800"
           x-text="r && t('controls.split', { m: fmt(r.males), f: fmt(r.females), r: fmt(params.male_ratio) })"></p>

        <fieldset>
            <legend class="mb-2 font-semibold">{{ __('sim.controls.zone') }}</legend>
            <div class="grid grid-cols-1 gap-2 min-[400px]:grid-cols-3">
                @foreach (['benin', 'togo', 'world'] as $zone)
                    <button type="button" class="{{ $chip }}" :class="d.zone === '{{ $zone }}' ? {{ $chipOn }} : {{ $chipOff }}" :aria-pressed="d.zone === '{{ $zone }}'" @click="d.zone = '{{ $zone }}'">
                        <span class="truncate">{{ __('sim.zones.'.$zone) }}</span>
                        <span class="text-xs font-normal" x-text="t('controls.per_chick', { price: money(chickPrice('{{ $zone }}')) })"></span>
                    </button>
                @endforeach
            </div>
        </fieldset>
        <fieldset>
            <legend class="mb-2 font-semibold">{{ __('sim.controls.scenario') }}</legend>
            <div class="grid grid-cols-3 gap-2">
                <template x-for="s in scenarios" :key="s">
                    <button type="button" class="{{ $chip }}" :class="d.scenario === s ? {{ $chipOn }} : {{ $chipOff }}" :aria-pressed="d.scenario === s" @click="d.scenario = s">
                        <span class="w-full truncate" x-text="t('scenarios.' + s)"></span>
                    </button>
                </template>
            </div>
            <p class="mt-2 text-sm text-stone-600 dark:text-stone-400" x-text="t('controls.scenario_hint', { m: fmt(params['mort_' + d.scenario]), f: fmt(params['feedf_' + d.scenario]), p: fmt(params['pricef_' + d.scenario]) })"></p>
        </fieldset>
        <p class="flex items-center justify-between gap-2 rounded-xl bg-brand-50 px-3 py-2 font-semibold text-brand-800 dark:bg-brand-800/30 dark:text-brand-100">
            <span>{{ __('sim.controls.chicks_cost') }}</span><span class="tabular-nums" x-text="r ? money(r.lines.chicks) : '—'"></span>
        </p>
    </div>

    {{-- Chapter 2: surfaces from densities --}}
    <div x-show="node.kind === 'building' && r" class="card space-y-3 p-4">
        <div class="grid grid-cols-1 gap-2 min-[400px]:grid-cols-2">
            <div class="rounded-xl bg-stone-100 p-3 dark:bg-stone-800">
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.surface_brooder') }}</p>
                <p class="text-xl font-extrabold tabular-nums" x-text="r && fmt(r.surfaceBrooder, 1) + ' m²'"></p>
                <p class="text-sm" x-text="t('controls.density', { d: fmt(params.density_brooder) })"></p>
            </div>
            <div class="rounded-xl bg-stone-100 p-3 dark:bg-stone-800">
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.surface_adult') }}</p>
                <p class="text-xl font-extrabold tabular-nums" x-text="r && fmt(r.surfaceAdult, 1) + ' m²'"></p>
                <p class="text-sm" x-text="r && t('controls.density', { d: fmt(r.density) })"></p>
            </div>
        </div>
        <p class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-brand-50 px-3 py-2 text-sm font-semibold text-brand-800 dark:bg-brand-800/30 dark:text-brand-100">
            <span>{{ __('sim.controls.invest') }}</span><span class="tabular-nums" x-text="r && money(r.surfaceAdult * params.build_cost_m2)"></span>
        </p>
        <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.surface_note') }}</p>
    </div>

    {{-- Chapter 3: temperature programme --}}
    <div x-show="node.kind === 'heat'" class="card space-y-3 p-4">
        <ol class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <template x-for="w in tempWeeks" :key="w.w">
                <li class="rounded-xl bg-gradient-to-b from-red-50 to-amber-50 p-3 text-center dark:from-red-950/40 dark:to-amber-950/30">
                    <p class="text-sm text-stone-600 dark:text-stone-400" x-text="t('controls.temp_week', { w: w.w })"></p>
                    <p class="text-lg font-extrabold tabular-nums text-red-700 dark:text-red-300" x-text="t('controls.temp_range', { min: w.min, max: w.max })"></p>
                </li>
            </template>
        </ol>
        <p class="text-sm text-stone-600 dark:text-stone-400" x-text="t('controls.temp_note', { w: fmt(params.brooding_weeks ?? 3) })"></p>
    </div>

    {{-- Chapter 4: bag size + ration comparison --}}
    <div x-show="node.kind === 'feed' && r" class="card space-y-3 p-4">
        <fieldset>
            <legend class="mb-2 font-semibold">{{ __('sim.controls.bag') }}</legend>
            <div class="grid grid-cols-2 gap-2">
                @foreach ([25, 50] as $bag)
                    <button type="button" class="{{ $chip }}" :class="d.bagKg === {{ $bag }} ? {{ $chipOn }} : {{ $chipOff }}" :aria-pressed="d.bagKg === {{ $bag }}" @click="d.bagKg = {{ $bag }}">{{ __('sim.controls.bag_kg', ['kg' => $bag]) }}</button>
                @endforeach
            </div>
        </fieldset>
        <p class="rounded-xl bg-stone-100 px-3 py-2 text-sm tabular-nums dark:bg-stone-800"
           x-text="t('controls.feed_formula', { n: fmt(d.flock), g: fmt(rationOptions[0]?.g1 ?? 0), kg: fmt((rationOptions[0]?.day1 ?? 0), 1) })"></p>
        <div class="space-y-2">
            <template x-for="o in rationOptions" :key="o.ration">
                <div class="rounded-xl border-2 p-3" :class="o.ration === d.ration ? 'border-brand-600 bg-brand-50/60 dark:bg-brand-800/20' : 'border-stone-200 dark:border-stone-700'">
                    <p class="flex flex-wrap items-center justify-between gap-1 font-bold">
                        <span x-text="t('rations.' + o.ration)"></span>
                        <span x-show="o.ration === d.ration" class="sim-badge sim-badge-apg">{{ __('sim.controls.current') }}</span>
                    </p>
                    <dl class="mt-1 grid grid-cols-2 gap-x-3 gap-y-1 text-sm sm:grid-cols-5">
                        <div><dt class="text-stone-500">{{ __('sim.controls.per_day') }}</dt><dd class="font-semibold tabular-nums" x-text="kg(o.day1)"></dd></div>
                        <div><dt class="text-stone-500">{{ __('sim.controls.week1') }}</dt><dd class="font-semibold tabular-nums" x-text="kg(o.week1)"></dd></div>
                        <div><dt class="text-stone-500">{{ __('sim.controls.cycle') }}</dt><dd class="font-semibold tabular-nums" x-text="o.res ? kg(o.res.feedKg) : '—'"></dd></div>
                        <div><dt class="text-stone-500">{{ __('sim.controls.bags') }}</dt><dd class="font-semibold tabular-nums" x-text="o.res ? fmt(o.res.bags) + ' × ' + d.bagKg + ' kg' : '—'"></dd></div>
                        <div><dt class="text-stone-500">{{ __('sim.controls.cost') }}</dt><dd class="font-semibold tabular-nums" x-text="o.res ? money(o.res.lines.feed) : '—'"></dd></div>
                    </dl>
                </div>
            </template>
        </div>
    </div>

    {{-- Chapter 6 (eggs / breeding): laying --}}
    <div x-show="node.kind === 'laying' && r" class="card space-y-3 p-4">
        <p class="rounded-xl bg-stone-100 px-3 py-2 font-semibold tabular-nums dark:bg-stone-800"
           x-text="r && t('controls.eggs_formula', { f: fmt(r.females), r: fmt(r.layRatePct, 1), e: fmt(eggsPerDay) })"></p>
        <div class="grid grid-cols-1 gap-2 min-[400px]:grid-cols-2">
            <div class="rounded-xl bg-amber-50 p-3 dark:bg-amber-950/30">
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.eggs_female') }}</p>
                <p class="text-xl font-extrabold tabular-nums" x-text="r && fmt(r.eggsPerFemale)"></p>
                <p class="text-sm" x-text="t('controls.eggs_target', { t: fmt(params.laying_target_eggs) })"></p>
            </div>
            <div class="rounded-xl bg-amber-50 p-3 dark:bg-amber-950/30">
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.lay_days') }}</p>
                <p class="text-xl font-extrabold tabular-nums" x-text="r && fmt(r.layDays)"></p>
                <p class="text-sm" x-text="t('scenarios.' + d.scenario)"></p>
            </div>
        </div>
    </div>

    {{-- Chapter 6 (breeding): egg allocation + hatchability denominator --}}
    <div x-show="node.kind === 'incubation'" class="card space-y-4 p-4">
        <fieldset class="space-y-2">
            <legend class="font-semibold">{{ __('sim.controls.alloc') }}</legend>
            @foreach (['table', 'incubate', 'sell'] as $part)
                <div>
                    <label for="sim-alloc-{{ $part }}" class="flex justify-between text-sm"><span>{{ __('sim.controls.alloc_'.$part) }}</span><span class="font-bold tabular-nums" x-text="d.alloc.{{ $part }} + ' %'"></span></label>
                    <input id="sim-alloc-{{ $part }}" type="range" class="sim-range" min="0" max="100" step="5" :value="d.alloc.{{ $part }}" :style="`--fill: ${d.alloc.{{ $part }}}%`"
                           @input="setAlloc('{{ $part }}', $event.target.value)" :aria-valuetext="d.alloc.{{ $part }} + ' %'">
                </div>
            @endforeach
            <p class="text-sm font-bold" :class="allocTotal > 100 ? 'text-red-600' : 'text-green-700 dark:text-green-400'" x-text="t('controls.alloc_total', { t: allocTotal })"></p>
        </fieldset>
        <fieldset>
            <legend class="mb-2 font-semibold">{{ __('sim.controls.denominator') }}</legend>
            <div class="grid grid-cols-2 gap-2">
                @foreach (['fertile', 'loaded'] as $base)
                    <button type="button" class="{{ $chip }}" :class="d.hatchBase === '{{ $base }}' ? {{ $chipOn }} : {{ $chipOff }}" :aria-pressed="d.hatchBase === '{{ $base }}'" @click="d.hatchBase = '{{ $base }}'">{{ __('sim.controls.base_'.$base) }}</button>
                @endforeach
            </div>
        </fieldset>
        <p x-show="r" class="rounded-xl bg-stone-100 px-3 py-2 text-sm font-semibold tabular-nums dark:bg-stone-800" x-text="hatchFormula"></p>
        <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('sim.controls.example') }}</p>
    </div>

    {{-- Chapter 7: reference prices --}}
    <div x-show="node.kind === 'sale' && r" class="card space-y-1 p-4 text-sm">
        <p class="font-semibold">{{ __('sim.controls.sale_ref') }}</p>
        <p x-show="d.goal === 'chair'" x-text="t('controls.frozen_ref', { p: money(params.frozen_price_kg) + '/kg' })"></p>
        <p x-show="d.goal !== 'chair'" x-text="t('controls.eggs_ref', { p: money(params.egg_price) })"></p>
        <p x-show="d.goal === 'repro'" x-text="t('controls.chick_ref', { p: money(params.chick_sale_price) })"></p>
    </div>
</div>
