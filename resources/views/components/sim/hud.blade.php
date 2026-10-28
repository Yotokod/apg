{{-- Live HUD: 2×2 on phones, 4 columns from sm; day timeline bar underneath. Values tween with GSAP. --}}
<div class="mb-4 space-y-2" aria-label="{{ __('sim.ui.progress') }}">
    <dl class="grid grid-cols-2 gap-2 sm:grid-cols-4">
        @foreach ([
            ['fa-wallet', 'sim.hud.spent', 'money(hud.spent)', 'text-ink-900 dark:text-white'],
            ['fa-dove', 'sim.hud.alive', 'fmt(hud.alive)', 'text-ink-900 dark:text-white'],
            ['fa-heart-crack', 'sim.hud.mortality', 'pct(hud.mortalityPct)', "hud.mortalityPct >= 15 ? 'text-red-600' : 'text-ink-900 dark:text-white'"],
            ['fa-wheat-awn', 'sim.hud.feed', 'kg(hud.feedKg)', 'text-ink-900 dark:text-white'],
        ] as [$icon, $label, $value, $color])
            <div class="card flex min-w-0 items-center gap-2 px-3 py-2">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-brand-800/30 dark:text-brand-100"><i class="fa-solid {{ $icon }}" aria-hidden="true"></i></span>
                <div class="min-w-0">
                    <dt class="truncate text-xs text-stone-500 dark:text-stone-400">{{ __($label) }}</dt>
                    <dd class="truncate text-base font-extrabold tabular-nums" @if (str_contains($color, '?')) :class="{{ $color }}" @else class="{{ $color }}" @endif x-text="{{ $value }}"></dd>
                </div>
            </div>
        @endforeach
    </dl>
    <div class="flex items-center gap-3">
        <i class="fa-solid fa-calendar-day text-brand-600" aria-hidden="true"></i>
        <div class="h-2 flex-1 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800" role="progressbar" aria-valuemin="0" :aria-valuemax="totalDays" :aria-valuenow="Math.round(hud.day)" aria-label="{{ __('sim.hud.day') }}">
            <div class="h-full rounded-full bg-gradient-to-r from-brand-500 to-brand-700" :style="`width: ${totalDays ? Math.min(100, hud.day / totalDays * 100) : 0}%`"></div>
        </div>
        <span class="shrink-0 text-sm font-bold tabular-nums" x-text="t('hud.day_of', { d: fmt(hud.day), total: fmt(totalDays) })"></span>
    </div>
</div>
