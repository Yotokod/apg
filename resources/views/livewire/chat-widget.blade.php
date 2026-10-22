<div wire:poll.10s
     x-data="{ open: false, shown: true, scrolling: false, t: null, iv: null,
        init() {
            const hideWhileScrolling = () => { this.scrolling = true; this.shown = false; clearTimeout(this.t); this.t = setTimeout(() => { this.scrolling = false; this.shown = true }, 5000) };
            window.addEventListener('scroll', hideWhileScrolling, { passive: true });
            // idle: alternate show/hide every 5s unless scrolling or chat open
            this.iv = setInterval(() => { if (!this.scrolling && !this.open) this.shown = !this.shown }, 5000);
        } }"
     class="fixed bottom-4 right-4 z-40">
    <div x-show="open" x-cloak x-transition class="card mb-3 flex h-[28rem] max-h-[70vh] w-80 max-w-[calc(100vw-2rem)] flex-col overflow-hidden">
        <div class="flex items-center justify-between bg-brand-600 px-4 py-3 text-white">
            <span class="font-bold"><i class="fa-solid fa-headset"></i> {{ __('chat.title') }}</span>
            <button @click="open = false" aria-label="{{ __('nav.close') }}"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="flex-1 space-y-2 overflow-y-auto p-3 text-sm" x-ref="box" x-effect="$nextTick(() => $refs.box.scrollTop = $refs.box.scrollHeight)">
            <p class="rounded-xl bg-stone-100 p-2 dark:bg-stone-800">{{ __('chat.hello') }}</p>
            @foreach ($messages as $m)
                <p class="max-w-[85%] rounded-xl p-2 {{ $m->role === 'user' ? 'ml-auto bg-brand-600 text-white' : 'bg-stone-100 dark:bg-stone-800' }}">{{ $m->content }}</p>
            @endforeach
            <div wire:loading wire:target="send" class="skeleton h-8 w-24"></div>
        </div>
        <form wire:submit="send" class="flex gap-2 border-t border-stone-200 p-2 dark:border-stone-800">
            <input wire:model="input" class="input" placeholder="{{ __('chat.placeholder') }}" aria-label="{{ __('chat.placeholder') }}">
            <button class="btn-primary !px-3" aria-label="{{ __('chat.send') }}"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
    <button @click="open = !open" class="btn-primary h-14 w-14 !rounded-full shadow-lg transition-all duration-500"
            :class="(shown || open) ? 'translate-x-0 opacity-100' : 'translate-x-24 opacity-0'" aria-label="{{ __('chat.title') }}">
        <i class="fa-solid fa-comment-dots text-xl"></i>
    </button>
</div>
