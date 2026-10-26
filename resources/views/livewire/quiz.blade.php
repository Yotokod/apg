<section class="card p-6" aria-live="polite">
    <h2 class="mb-4 flex items-center gap-2 text-lg font-extrabold"><i class="fa-solid fa-circle-question text-accent-500"></i> {{ __('quiz.title') }}</h2>
    @if ($finished)
        <div class="space-y-3 text-center">
            <i class="fa-solid {{ $percent >= 60 ? 'fa-trophy text-accent-500' : 'fa-rotate text-stone-400' }} text-5xl"></i>
            <p class="text-2xl font-extrabold">{{ $percent }}%</p>
            <p class="text-stone-600 dark:text-stone-400">{{ $percent >= 60 ? __('quiz.pass') : __('quiz.retry') }}</p>
            <button wire:click="restart" class="btn-ghost">{{ __('quiz.restart') }}</button>
        </div>
    @elseif ($node)
        <div class="mb-4 h-2 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-800" role="progressbar" aria-valuenow="{{ $step }}">
            <div class="h-full bg-brand-600 transition-all" style="width: {{ min(100, $step * 20) }}%"></div>
        </div>
        <p class="mb-1 text-xs font-semibold uppercase text-stone-500">{{ __('quiz.step', ['n' => $step]) }}</p>
        <p class="mb-4 text-lg font-semibold">{{ $node->tr('question') }}</p>
        <div class="grid gap-2">
            @foreach ($node->options as $o)
                @php
                    $state = $picked ? ($o->is_correct ? 'border-green-600 bg-green-50 dark:bg-green-900/20' : ($picked === $o->id ? 'border-red-500 bg-red-50 dark:bg-red-900/20' : 'opacity-60')) : 'hover:border-brand-600';
                @endphp
                <button wire:click="pick({{ $o->id }})" @disabled($picked) class="min-h-11 rounded-xl border-2 border-stone-200 px-4 py-3 text-left transition dark:border-stone-700 {{ $state }}">{{ $o->tr('label') }}</button>
            @endforeach
        </div>
        @if ($picked)
            @if ($node->explanation)<p class="mt-3 rounded-xl bg-brand-50 p-3 text-sm dark:bg-stone-800"><i class="fa-solid fa-lightbulb text-accent-500"></i> {{ $node->tr('explanation') }}</p>@endif
            <button wire:click="next" class="btn-primary mt-4">{{ __('quiz.continue') }} <i class="fa-solid fa-arrow-right"></i></button>
        @endif
    @endif
</section>
