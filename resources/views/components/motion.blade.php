{{-- Lottie player with accessible controls. Usage: <x-motion :src="asset('storage/'.$file)" :label="$title" /> --}}
@props(['src', 'label' => '', 'loop' => true])
<figure data-motion class="card overflow-hidden" role="group" aria-label="{{ $label }}">
    <div class="aspect-video bg-gradient-to-br from-brand-50 to-white dark:from-stone-900 dark:to-stone-800">
        <div data-lottie="{{ $src }}" data-loop="{{ $loop ? 'true' : 'false' }}" class="h-full w-full" role="img" aria-label="{{ $label }}"></div>
    </div>
    <figcaption class="flex items-center justify-between gap-2 border-t border-stone-200 px-3 py-2 text-sm dark:border-stone-800">
        <span class="truncate text-stone-600 dark:text-stone-400"><i class="fa-solid fa-wand-magic-sparkles text-brand-600"></i> {{ $label }}</span>
        <span class="flex gap-1">
            <button type="button" class="btn-ghost !min-h-9 !px-3" onclick="apgMotion.toggle(this)" aria-label="{{ __('motion.toggle') }}"><i class="fa-solid fa-circle-pause"></i></button>
            <button type="button" class="btn-ghost !min-h-9 !px-3" onclick="apgMotion.replay(this)" aria-label="{{ __('motion.replay') }}"><i class="fa-solid fa-rotate-right"></i></button>
        </span>
    </figcaption>
</figure>
