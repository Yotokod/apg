<div>
    <x-page-hero :title="__('nav.simulator')" :subtitle="__('sim.subtitle')" icon="fa-chart-line" />
    <div class="mx-auto max-w-4xl space-y-5 px-4 py-8">
        {{-- Live dashboard (animated counters) --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([['cash', 'sim.cash', 'fa-wallet', fcfa($state['cash'])], ['birds', 'sim.birds', 'fa-dove', $state['birds']], ['mortality', 'sim.mortality', 'fa-skull-crossbones', $state['mortality'].'%'], ['revenue_pct', 'sim.price_bonus', 'fa-arrow-trend-up', ($state['revenue_pct'] >= 0 ? '+' : '').$state['revenue_pct'].'%']] as [$k, $label, $ic, $val])
                <div class="card p-3 text-center" wire:key="hud-{{ $k }}-{{ $val }}" x-data x-init="$el.animate([{transform:'scale(1.06)'},{transform:'scale(1)'}],{duration:300})">
                    <i class="fa-solid {{ $ic }} text-brand-600"></i>
                    <p class="truncate text-lg font-extrabold">{{ $val }}</p>
                    <p class="truncate text-xs text-stone-500">{{ __($label) }}</p>
                </div>
            @endforeach
        </div>

        @if ($step)
            <div class="h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800"><div class="h-full bg-brand-600 transition-all duration-500" style="width: {{ ($index / max(1, $steps->count())) * 100 }}%"></div></div>
            <section class="card space-y-4 p-6" wire:key="step-{{ $step->id }}">
                <p class="text-xs font-semibold uppercase text-stone-500">{{ __('sim.step', ['n' => $index + 1, 'total' => $steps->count()]) }}</p>
                <h2 class="text-xl font-extrabold">{{ $step->tr('title') }}</h2>
                <p class="text-stone-600 dark:text-stone-400">{{ $step->tr('body') }}</p>
                <div class="grid gap-2">
                    @foreach ($step->choices as $c)
                        <button wire:click="choose({{ $c->id }})" class="min-h-11 rounded-xl border-2 border-stone-200 px-4 py-3 text-left transition hover:border-brand-600 dark:border-stone-700">
                            {{ $c->tr('label') }}
                            <span class="block text-xs text-stone-500">{{ $c->cash ? fcfa($c->cash) : '' }}</span>
                        </button>
                    @endforeach
                </div>
                @if ($history) <button wire:click="back" class="btn-ghost"><i class="fa-solid fa-rotate-left"></i> {{ __('sim.back') }}</button> @endif
            </section>
        @elseif ($result)
            <section class="card space-y-4 p-6 text-center" x-data x-init="$el.animate([{opacity:0,transform:'translateY(12px)'},{opacity:1,transform:'none'}],{duration:400})">
                <i class="fa-solid {{ $result['profit'] >= 0 ? 'fa-trophy text-accent-500' : 'fa-triangle-exclamation text-red-500' }} text-5xl"></i>
                <h2 class="text-2xl font-extrabold">{{ __('sim.result') }}</h2>
                <div class="grid gap-3 text-left sm:grid-cols-3">
                    <div class="rounded-xl bg-stone-100 p-3 dark:bg-stone-800"><p class="text-xs text-stone-500">{{ __('sim.alive') }}</p><p class="text-xl font-extrabold">{{ $result['alive'] }}</p></div>
                    <div class="rounded-xl bg-stone-100 p-3 dark:bg-stone-800"><p class="text-xs text-stone-500">{{ __('sim.sales') }}</p><p class="text-xl font-extrabold">{{ fcfa($result['sales']) }}</p></div>
                    <div class="rounded-xl p-3 {{ $result['profit'] >= 0 ? 'bg-brand-100 text-brand-700' : 'bg-red-100 text-red-700' }}"><p class="text-xs">{{ __('sim.profit') }}</p><p class="text-xl font-extrabold">{{ fcfa($result['profit']) }} ({{ $result['roi'] }}%)</p></div>
                </div>
                <div class="flex flex-wrap justify-center gap-2">
                    <button wire:click="back" class="btn-ghost"><i class="fa-solid fa-rotate-left"></i> {{ __('sim.back') }}</button>
                    <button wire:click="restart" class="btn-primary">{{ __('sim.restart') }}</button>
                    <a href="{{ route('courses') }}" wire:navigate class="btn-accent">{{ __('sim.learn') }}</a>
                </div>
            </section>
        @else
            <p class="card p-8 text-center text-stone-500">{{ __('sim.empty') }}</p>
        @endif
    </div>

    {{-- Bad-decision popup --}}
    @if ($warn)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" role="dialog" aria-modal="true">
            <div class="card w-full max-w-md space-y-3 p-6" x-data x-init="$el.animate([{transform:'scale(.9)',opacity:0},{transform:'none',opacity:1}],{duration:250})">
                <h3 class="flex items-center gap-2 text-lg font-extrabold text-red-600"><i class="fa-solid fa-triangle-exclamation"></i> {{ __('sim.bad_title') }}</h3>
                <p>{{ $warn->tr('tip') }}</p>
                <div class="flex flex-wrap justify-end gap-2">
                    <button wire:click="dismissWarning" class="btn-primary"><i class="fa-solid fa-rotate-left"></i> {{ __('sim.choose_again') }}</button>
                    <button wire:click="confirmBad" class="btn-ghost">{{ __('sim.continue_anyway') }}</button>
                </div>
            </div>
        </div>
    @endif
</div>
