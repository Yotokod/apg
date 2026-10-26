@props(['items'])
<div x-data="{ prev() { this.$refs.track.scrollBy({ left: -this.$refs.track.clientWidth * .8, behavior: 'smooth' }) },
               next() { this.$refs.track.scrollBy({ left: this.$refs.track.clientWidth * .8, behavior: 'smooth' }) } }" class="relative">
    <div x-ref="track" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth px-4 pb-3 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden" tabindex="0" role="region" aria-label="{{ __('home.testimonials') }}">
        @foreach ($items as $t)
            <figure class="card flex w-[85%] shrink-0 snap-start flex-col gap-3 p-5 sm:w-[calc(50%-.5rem)] lg:w-[calc(33.333%-.75rem)]">
                <i class="fa-solid fa-quote-left text-xl text-accent-500" aria-hidden="true"></i>
                <blockquote class="line-clamp-5 text-sm leading-relaxed text-stone-700 dark:text-stone-300">{{ $t->text }}</blockquote>
                <figcaption class="mt-auto flex items-center gap-3 pt-2">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-600 font-bold text-white" aria-hidden="true">{{ mb_strtoupper(mb_substr($t->name, 0, 1)) }}</span>
                    <span class="min-w-0 text-sm"><strong class="block truncate">{{ $t->name }}</strong><span class="block truncate text-stone-600 dark:text-stone-400">{{ $t->country }}</span></span>
                </figcaption>
            </figure>
        @endforeach
    </div>
    <div class="mt-2 flex justify-end gap-2">
        <button type="button" @click="prev()" class="btn-ghost !px-3" aria-label="{{ __('home.prev') }}"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
        <button type="button" @click="next()" class="btn-ghost !px-3" aria-label="{{ __('home.next') }}"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
    </div>
</div>
