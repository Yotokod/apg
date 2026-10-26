<button type="button" x-data="{ on: false }" x-show="on" x-cloak x-transition.opacity
        @scroll.window.passive="on = window.scrollY > 600" @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-24 left-4 z-40 flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-ink-900 shadow-md transition hover:bg-stone-100 dark:border-stone-700 dark:bg-stone-900 dark:text-white dark:hover:bg-stone-800"
        aria-label="{{ __('footer.top') }}"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></button>
