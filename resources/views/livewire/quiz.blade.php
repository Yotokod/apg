@php
    $scenes = ['egg-hatch', 'feeding', 'chick-growth', 'incubator', 'vaccination'];
    $t = [
        'question' => __('lesson.quiz.question', ['n' => ':n']),
        'answered' => __('lesson.quiz.answered', ['n' => ':n', 'total' => ':total']),
        'goodAnswers' => __('lesson.quiz.good_answers', ['n' => ':n', 'total' => ':total']),
        'rightAnswer' => __('lesson.quiz.right_answer', ['answer' => ':answer']),
        'error' => __('lesson.quiz.error'),
    ];
@endphp
<div>
    <section wire:ignore x-data="apgQuiz(@js($config + ['t' => $t, 'scenes' => count($scenes)]))" data-quiz
             class="card relative overflow-hidden" aria-labelledby="quiz-title-{{ $lessonId }}">
        {{-- HUD: progress ring, score, streak, back --}}
        <header class="flex flex-wrap items-center gap-3 border-b border-stone-200 p-3 sm:p-4 dark:border-stone-800">
            <div class="relative size-12 shrink-0" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="ringPercent" aria-label="{{ __('lesson.quiz.hud') }}">
                <svg viewBox="0 0 44 44" class="size-12 -rotate-90" aria-hidden="true">
                    <circle cx="22" cy="22" r="18" fill="none" stroke-width="5" class="stroke-stone-200 dark:stroke-stone-700"/>
                    <circle cx="22" cy="22" r="18" fill="none" stroke-width="5" stroke-linecap="round" class="stroke-brand-600 transition-[stroke-dashoffset] duration-500"
                            stroke-dasharray="113.1" :stroke-dashoffset="113.1 * (1 - ringPercent / 100)" stroke-dashoffset="113.1"/>
                </svg>
                <span class="absolute inset-0 grid place-items-center text-[11px] font-extrabold" x-text="ringPercent + '%'">0%</span>
            </div>
            <div class="min-w-0 flex-1 basis-40">
                <h2 id="quiz-title-{{ $lessonId }}" class="flex items-center gap-2 truncate text-base font-extrabold sm:text-lg"><i class="fa-solid fa-gamepad text-brand-600" aria-hidden="true"></i> {{ __('lesson.quiz.title') }}</h2>
                <p class="truncate text-xs text-stone-600 dark:text-stone-400" x-text="fmt(t.answered, { n: answered, total })"></p>
            </div>
            <div class="flex items-center gap-1.5 text-sm font-bold">
                <span class="inline-flex min-h-9 items-center gap-1 rounded-full bg-emerald-50 px-2.5 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300" title="{{ __('lesson.quiz.score') }}">
                    <i class="fa-solid fa-star" aria-hidden="true"></i><span class="sr-only">{{ __('lesson.quiz.score') }}</span><span data-score x-text="good">0</span>
                </span>
                <span class="inline-flex min-h-9 items-center gap-1 rounded-full bg-brand-50 px-2.5 text-brand-800 dark:bg-stone-800 dark:text-brand-500" title="{{ __('lesson.quiz.streak') }}">
                    <i class="fa-solid fa-fire" aria-hidden="true"></i><span class="sr-only">{{ __('lesson.quiz.streak') }}</span><span data-streak x-text="streak">0</span>
                </span>
                <button type="button" @click="back()" x-show="canBack && (phase === 'ask' || phase === 'result')" style="display: none" :disabled="busy"
                        class="btn-ghost !min-h-9 !px-3" aria-label="{{ __('lesson.quiz.back') }}" title="{{ __('lesson.quiz.back') }}">
                    <i class="fa-solid fa-arrow-rotate-left" aria-hidden="true"></i>
                </button>
            </div>
        </header>

        <p class="sr-only" aria-live="assertive" x-text="announce"></p>

        @if (! $config['node'] && ! $config['finished'])
            <p class="p-6 text-center text-stone-600 dark:text-stone-400">{{ __('lesson.quiz.empty') }}</p>
        @endif

        {{-- Question stage --}}
        <div x-show="phase !== 'result' && node" @if (! $config['node']) style="display: none" @endif class="p-3 sm:p-5">
            <div data-question-card class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-ink-900 via-ink-800 to-brand-700 p-4 text-white sm:p-5">
                <div class="pointer-events-none absolute -right-6 -bottom-6 w-36 opacity-90 sm:w-48" aria-hidden="true">
                    @foreach ($scenes as $s)
                        <div x-show="sceneIndex === {{ $loop->index }}" @if (! $loop->first) style="display: none" @endif>
                            <x-dynamic-component :component="'illustrations.'.$s" />
                        </div>
                    @endforeach
                </div>
                <p class="relative text-xs font-bold uppercase tracking-wide text-amber-200" x-text="fmt(t.question, { n: answered + (phase === 'ask' || phase === 'checking' ? 1 : 0) })"></p>
                <p id="quiz-q-{{ $lessonId }}" data-question class="relative mt-1 max-w-[80%] text-lg font-extrabold leading-snug sm:text-xl" x-text="node?.question">{{ $config['node']['question'] ?? '' }}</p>
            </div>

            <div role="radiogroup" aria-labelledby="quiz-q-{{ $lessonId }}" class="mt-3 grid gap-2.5 sm:grid-cols-2" data-options>
                <template x-for="(o, idx) in (node?.options || [])" :key="o.id">
                    <button type="button" role="radio" data-option :data-id="o.id"
                            :aria-checked="selected === o.id ? 'true' : 'false'" :tabindex="idx === focusIndex ? 0 : -1"
                            :disabled="phase !== 'ask'"
                            @click="pick(o.id, $event.currentTarget)" @keydown="onKey($event, idx)" @focus="focusIndex = idx"
                            class="lsn-option group flex min-h-16 w-full items-center gap-3 rounded-2xl border-2 border-stone-200 bg-white p-3 text-left font-semibold transition-colors dark:border-stone-700 dark:bg-stone-900"
                            :class="optionClass(o.id)">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 text-base font-extrabold text-brand-700 dark:bg-stone-800 dark:text-brand-500" aria-hidden="true">
                            <i class="fa-solid" :class="optionIcon(o.id, idx)"></i>
                        </span>
                        <span class="min-w-0 flex-1 break-words" x-text="o.label"></span>
                    </button>
                </template>
            </div>

            <div x-show="phase === 'feedback'" style="display: none" data-feedback class="mt-3 rounded-2xl p-3 sm:p-4" :class="feedback?.correct ? 'bg-emerald-50 dark:bg-emerald-900/25' : 'bg-red-50 dark:bg-red-900/25'">
                <p class="flex items-center gap-2 font-extrabold" :class="feedback?.correct ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300'">
                    <i class="fa-solid" :class="feedback?.correct ? 'fa-circle-check' : 'fa-circle-xmark'" aria-hidden="true"></i>
                    <span x-text="feedback?.correct ? @js(__('lesson.quiz.correct')) : @js(__('lesson.quiz.wrong'))"></span>
                </p>
                <p x-show="!feedback?.correct && rightLabel" class="mt-1 text-sm" x-text="fmt(t.rightAnswer, { answer: rightLabel })"></p>
                <p x-show="feedback?.explanation" class="mt-2 flex gap-2 text-sm"><i class="fa-solid fa-lightbulb mt-0.5 text-amber-500" aria-hidden="true"></i><span x-text="feedback?.explanation"></span></p>
            </div>

            <p x-show="error" style="display: none" class="mt-3 rounded-xl bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/25 dark:text-red-300" role="alert" x-text="error"></p>
            <p x-show="phase === 'checking'" style="display: none" class="mt-3 text-sm text-stone-600 dark:text-stone-400"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> {{ __('lesson.quiz.checking') }}</p>
        </div>

        {{-- Sticky continue --}}
        <div x-show="phase === 'feedback'" style="display: none" class="sticky bottom-0 z-10 border-t border-stone-200 bg-white/95 p-3 backdrop-blur dark:border-stone-800 dark:bg-stone-900/95">
            <button type="button" x-ref="continue" @click="proceed()" class="btn-primary w-full">
                <span x-text="feedback?.finished ? @js(__('lesson.quiz.finish')) : @js(__('lesson.quiz.continue'))"></span> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
            </button>
        </div>

        {{-- Result --}}
        <div x-show="phase === 'result'" @if (! $config['finished']) style="display: none" @endif data-result class="space-y-4 p-4 text-center sm:p-6">
            <div data-result-badge class="mx-auto grid size-24 place-items-center rounded-full text-4xl" :class="passed ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30' : 'bg-stone-100 text-stone-500 dark:bg-stone-800'">
                <i class="fa-solid" :class="passed ? 'fa-trophy' : 'fa-book-open-reader'" aria-hidden="true"></i>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-stone-600 dark:text-stone-400">{{ __('lesson.quiz.result') }}</p>
                <p class="text-4xl font-extrabold"><span data-result-percent x-text="shownPercent">{{ $config['percent'] }}</span>%</p>
                <p class="text-sm text-stone-600 dark:text-stone-400" x-text="fmt(t.goodAnswers, { n: good, total: answered })"></p>
                <p class="mt-2 font-semibold" x-text="passed ? @js(__('lesson.quiz.pass')) : @js(__('lesson.quiz.retry'))"></p>
            </div>
            @if ($config['review'])
                <a x-show="good < answered" href="{{ $config['review']['url'] }}" wire:navigate class="mx-auto flex max-w-md items-center gap-3 rounded-2xl border border-stone-200 p-3 text-left transition hover:border-brand-600 dark:border-stone-700">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 text-brand-700 dark:bg-stone-800 dark:text-brand-500"><i class="fa-solid fa-book-bookmark" aria-hidden="true"></i></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-xs font-bold uppercase text-stone-600 dark:text-stone-400">{{ __('lesson.quiz.review') }}</span>
                        <span class="block truncate font-bold">{{ $config['review']['title'] }}</span>
                    </span>
                    <span class="sr-only">{{ __('lesson.quiz.review_cta') }}</span><i class="fa-solid fa-chevron-right text-stone-400" aria-hidden="true"></i>
                </a>
            @endif
            <button type="button" @click="restart()" :disabled="busy" class="btn-ghost"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> {{ __('lesson.quiz.restart') }}</button>
        </div>
    </section>
</div>
