<nav aria-label="{{ __('sim.ui.progress') }}" class="mb-3">
    <ol class="grid grid-cols-4 gap-x-2 gap-y-2 sm:grid-cols-8 sm:gap-1">
        @foreach (__('sim.chapters') as $n => $label)
            <li class="min-w-0" :aria-current="chapter === {{ $n }} ? 'step' : null" title="{{ $label }}">
                <span class="block h-1.5 rounded-full transition-colors duration-500" :class="chapter >= {{ $n }} ? 'bg-brand-600' : 'bg-stone-200 dark:bg-stone-800'"></span>
                <span class="mt-1 flex min-w-0 items-center gap-1">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full text-xs font-bold transition-colors"
                          :class="chapter > {{ $n }} ? 'bg-brand-600 text-white' : (chapter === {{ $n }} ? 'bg-ink-900 text-white dark:bg-white dark:text-ink-900' : 'bg-stone-200 text-stone-600 dark:bg-stone-800 dark:text-stone-300')">
                        <i x-show="chapter > {{ $n }}" class="fa-solid fa-check text-[10px]" aria-hidden="true"></i><span x-show="chapter <= {{ $n }}">{{ $n }}</span>
                    </span>
                    <span class="truncate text-xs font-medium" :class="chapter === {{ $n }} ? 'text-ink-900 dark:text-white' : 'text-stone-500'">{{ $label }}</span>
                </span>
            </li>
        @endforeach
    </ol>
</nav>
