{{-- Reads items from the nearest [data-lb-group] of the clicked [data-lb-src] button, so it works with Livewire load-more. --}}
<div x-data="{ open: false, i: 0, items: [], last: null, loaded: false,
        show(el) { const g = el.closest('[data-lb-group]'); if (!g) return;
            const nodes = [...g.querySelectorAll('[data-lb-src]')];
            this.items = nodes.map(n => ({ src: n.dataset.lbSrc, alt: n.dataset.lbAlt, title: n.dataset.lbTitle }));
            this.i = Math.max(0, nodes.indexOf(el)); this.last = el; this.loaded = false; this.open = true;
            document.body.classList.add('overflow-hidden'); this.$nextTick(() => this.$refs.close.focus()); },
        hide() { this.open = false; document.body.classList.remove('overflow-hidden'); if (this.last) this.last.focus(); },
        go(d) { if (!this.items.length) return; this.loaded = false; this.i = (this.i + d + this.items.length) % this.items.length; },
        trap(e) { const f = [this.$refs.close, this.$refs.prev, this.$refs.next]; const k = f.indexOf(document.activeElement);
            e.preventDefault(); f[(k + (e.shiftKey ? -1 : 1) + f.length) % f.length].focus(); } }"
     @lightbox-open.window="show($event.detail.el)"
     @keydown.escape.window="open && hide()" @keydown.arrow-left.window="open && go(-1)" @keydown.arrow-right.window="open && go(1)">
    <div x-show="open" x-cloak x-transition.opacity.duration.150ms @keydown.tab="trap($event)"
         class="fixed inset-0 z-[70] flex flex-col bg-black/90 p-3 sm:p-6" role="dialog" aria-modal="true" aria-label="{{ __('gallery.dialog') }}">
        <div class="flex items-center justify-between gap-3 text-white">
            <p class="text-sm" x-text="(i + 1) + ' / ' + items.length" aria-live="polite"></p>
            <button type="button" x-ref="close" @click="hide()" class="flex h-11 w-11 items-center justify-center rounded-full bg-white/10 hover:bg-white/25" aria-label="{{ __('gallery.close') }}"><i class="fa-solid fa-xmark text-lg" aria-hidden="true"></i></button>
        </div>
        <div class="relative flex min-h-0 flex-1 items-center justify-center" @click.self="hide()">
            <div x-show="!loaded" class="skeleton absolute h-1/2 w-2/3 max-w-xl opacity-30" aria-hidden="true"></div>
            <template x-if="items[i]"><img :src="items[i].src" :alt="items[i].alt" @load="loaded = true" class="max-h-full max-w-full rounded-xl object-contain" draggable="false"></template>
            <button type="button" x-ref="prev" @click="go(-1)" class="absolute left-0 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25" aria-label="{{ __('gallery.prev') }}"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
            <button type="button" x-ref="next" @click="go(1)" class="absolute right-0 flex h-12 w-12 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25" aria-label="{{ __('gallery.next') }}"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
        </div>
        <p class="mt-3 truncate text-center text-sm text-white" x-text="items[i] ? items[i].title : ''"></p>
    </div>
</div>
