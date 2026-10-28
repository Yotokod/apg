{{-- Lottie JSON player (lazy, accessible controls, reduced-motion poster). JS: resources/js/lesson/lottie.js --}}
@props(['src', 'label' => '', 'transcript' => null])
<figure x-data="apgLottie({ src: @js($src) })" data-lottie-player class="card overflow-hidden" role="group" aria-label="{{ $label }}">
    <div class="relative aspect-video bg-gradient-to-br from-brand-50 to-white dark:from-stone-900 dark:to-stone-800">
        <div x-ref="stage" class="h-full w-full" role="img" aria-label="{{ $label }}"></div>
        <div x-show="status === 'idle' || status === 'loading'" class="absolute inset-0 grid place-items-center p-6" role="status">
            <div class="skeleton absolute inset-4"></div>
            <span class="relative flex items-center gap-2 text-sm font-semibold text-stone-600 dark:text-stone-300"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> {{ __('lesson.player.loading') }}</span>
        </div>
        <div x-show="status === 'error'" style="display: none" class="absolute inset-0 grid place-items-center p-4 text-center" role="alert">
            <div class="max-w-sm space-y-2">
                <i class="fa-solid fa-triangle-exclamation text-3xl text-brand-600" aria-hidden="true"></i>
                <p class="font-bold">{{ __('lesson.player.error') }}</p>
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('lesson.player.error_hint') }}</p>
                <button type="button" @click="retry()" class="btn-ghost"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i> {{ __('lesson.player.retry') }}</button>
            </div>
        </div>
    </div>
    <figcaption class="space-y-2 border-t border-stone-200 p-3 dark:border-stone-800">
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="toggle()" :disabled="status !== 'ready'" class="btn-primary !min-h-11 !px-3" :aria-label="playing ? @js(__('lesson.player.pause')) : @js(__('lesson.player.play'))">
                <i class="fa-solid" :class="playing ? 'fa-pause' : 'fa-play'" aria-hidden="true"></i>
            </button>
            <button type="button" @click="replay()" :disabled="status !== 'ready'" class="btn-ghost !min-h-11 !px-3" aria-label="{{ __('lesson.player.replay') }}"><i class="fa-solid fa-rotate-right" aria-hidden="true"></i></button>
            <input type="range" min="0" :max="total" step="1" x-model.number="frame" @input="scrub($event.target.value)" :disabled="status !== 'ready'"
                   class="h-11 min-w-0 flex-1 accent-brand-600" aria-label="{{ __('lesson.player.scrub') }}">
            <div class="flex rounded-xl border border-stone-300 p-0.5 dark:border-stone-700" role="group" aria-label="{{ __('lesson.player.speed') }}">
                @foreach ([1, 1.5] as $speed)
                    <button type="button" @click="setSpeed({{ $speed }})" :aria-pressed="speed === {{ $speed }}" class="min-h-10 rounded-lg px-2.5 text-xs font-bold" :class="speed === {{ $speed }} ? 'bg-ink-900 text-white dark:bg-white dark:text-ink-900' : ''">{{ $speed }}x</button>
                @endforeach
            </div>
            @if ($transcript)
                <button type="button" @click="transcript = ! transcript" :aria-expanded="transcript" class="btn-ghost !min-h-11 !px-3" :aria-label="transcript ? @js(__('lesson.player.transcript_hide')) : @js(__('lesson.player.transcript_show'))">
                    <i class="fa-solid fa-closed-captioning" aria-hidden="true"></i>
                </button>
            @endif
        </div>
        <p x-show="reduced" style="display: none" class="text-xs text-stone-600 dark:text-stone-400"><i class="fa-solid fa-universal-access" aria-hidden="true"></i> {{ __('lesson.player.reduced') }}</p>
        @if ($transcript)
            <div x-show="transcript" x-collapse style="display: none" class="rounded-xl bg-stone-50 p-3 text-sm dark:bg-stone-800">
                <p class="mb-1 font-bold">{{ __('lesson.player.transcript') }}</p>
                <p class="prose whitespace-pre-line">{{ $transcript }}</p>
            </div>
        @endif
    </figcaption>
</figure>
