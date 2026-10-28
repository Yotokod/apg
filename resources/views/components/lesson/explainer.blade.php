{{-- Built-in animated explainer: step-by-step scenes (autoplay timeline + manual controls). JS: resources/js/lesson/explainer.js --}}
@props(['scenes', 'title' => ''])
@php $count = count($scenes); @endphp
<section x-data="apgExplainer({ total: {{ $count }}, durations: @js(array_column($scenes, 'duration')) })"
         @keydown.left.prevent="prev()" @keydown.right.prevent="next()"
         data-explainer class="card overflow-hidden" aria-roledescription="carousel" aria-label="{{ __('lesson.explainer.label') }} : {{ $title }}">
    <div class="relative aspect-[4/3] bg-gradient-to-br from-brand-50 via-white to-amber-50 sm:aspect-video dark:from-stone-900 dark:via-stone-900 dark:to-stone-800">
        <div class="absolute inset-x-3 top-3 z-10 flex gap-1.5" aria-hidden="true">
            @for ($s = 0; $s < $count; $s++)
                <span class="h-1.5 flex-1 overflow-hidden rounded-full bg-ink-900/15 dark:bg-white/15">
                    <span class="lsn-bar-fill block h-full origin-left rounded-full bg-brand-600" :style="`transform: scaleX(${fill({{ $s }})})`" style="transform: scaleX(0)"></span>
                </span>
            @endfor
        </div>
        @foreach ($scenes as $s)
            <div data-step x-show="i === {{ $loop->index }}" @if (! $loop->first) style="display: none" @endif
                 class="absolute inset-0 px-4 pt-8 pb-3 sm:px-10 sm:pt-10" role="group" aria-roledescription="slide"
                 aria-label="{{ __('lesson.explainer.step', ['n' => $loop->iteration, 'total' => $count]) }}">
                <x-lesson.scene :illustration="$s['illustration']" :icon="$s['icon']" :label="$s['title']" class="mx-auto max-h-full max-w-full" />
            </div>
        @endforeach
    </div>

    <div class="border-t border-stone-200 p-4 sm:p-5 dark:border-stone-800">
        <div aria-live="polite" class="min-h-24">
            @foreach ($scenes as $s)
                <div data-narration x-show="i === {{ $loop->index }}" @if (! $loop->first) style="display: none" @endif>
                    <p class="text-xs font-bold uppercase tracking-wide text-brand-700 dark:text-brand-500">{{ __('lesson.explainer.step', ['n' => $loop->iteration, 'total' => $count]) }}</p>
                    <h2 class="mt-1 text-lg font-extrabold sm:text-xl">{{ $s['title'] }}</h2>
                    @if ($s['caption'] !== '')<p class="prose mt-1 text-sm sm:text-base">{{ $s['caption'] }}</p>@endif
                    @if ($s['points'])
                        <ul class="mt-3 grid gap-2 sm:grid-cols-2" aria-label="{{ __('lesson.explainer.points') }}">
                            @foreach ($s['points'] as $p)
                                <li class="flex items-start gap-2 rounded-xl bg-stone-50 px-3 py-2 text-sm dark:bg-stone-800"><i class="fa-solid fa-circle-check mt-0.5 text-emerald-600" aria-hidden="true"></i><span class="min-w-0">{{ $p }}</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-1.5">
                <button type="button" @click="prev()" :disabled="i === 0" class="btn-ghost !px-3" aria-label="{{ __('lesson.explainer.prev') }}"><i class="fa-solid fa-backward-step" aria-hidden="true"></i></button>
                <button type="button" @click="toggle()" class="btn-primary !px-4" :aria-label="playing ? @js(__('lesson.explainer.pause')) : (ended ? @js(__('lesson.explainer.replay')) : @js(__('lesson.explainer.play')))">
                    <i class="fa-solid" :class="playing ? 'fa-pause' : (ended ? 'fa-rotate-right' : 'fa-play')" aria-hidden="true"></i>
                </button>
                <button type="button" @click="next()" :disabled="i === total - 1" class="btn-ghost !px-3" aria-label="{{ __('lesson.explainer.next') }}"><i class="fa-solid fa-forward-step" aria-hidden="true"></i></button>
            </div>
            <div class="flex flex-wrap items-center gap-1">
                @for ($s = 0; $s < $count; $s++)
                    <button type="button" @click="go({{ $s }})" class="grid size-9 place-items-center" :aria-current="i === {{ $s }} ? 'step' : null" aria-label="{{ __('lesson.explainer.goto', ['n' => $s + 1]) }}">
                        <span class="block size-2.5 rounded-full transition" :class="i === {{ $s }} ? 'scale-125 bg-brand-600' : 'bg-stone-300 dark:bg-stone-600'"></span>
                    </button>
                @endfor
            </div>
        </div>
    </div>
</section>
