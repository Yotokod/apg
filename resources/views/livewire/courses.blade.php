<div>
    <x-page-hero :title="__('nav.courses')" :subtitle="__('courses.subtitle')" icon="fa-graduation-cap" />
    <div class="mx-auto grid max-w-7xl gap-5 px-4 py-8 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($courses as $c)
            <a href="{{ route('courses.show', $c->slug) }}" wire:navigate class="card flex flex-col overflow-hidden">
                <div class="flex h-36 items-center justify-center bg-gradient-to-br from-brand-600 to-brand-700 text-5xl text-white"><i class="fa-solid fa-book-open-reader"></i></div>
                <div class="flex flex-1 flex-col gap-2 p-4">
                    <h3 class="truncate font-bold">{{ $c->tr('title') }}</h3>
                    <p class="line-clamp-3 text-sm text-stone-600 dark:text-stone-400">{{ $c->tr('description') }}</p>
                    <div class="mt-auto flex items-center justify-between pt-2 text-sm">
                        <span><i class="fa-solid fa-list-check"></i> {{ $c->lessons_count }} {{ __('courses.lessons') }}</span>
                        <strong>{{ $c->price ? fcfa($c->price) : __('courses.free') }}</strong>
                    </div>
                </div>
            </a>
        @empty
            <p class="col-span-full py-12 text-center text-stone-500">{{ __('courses.empty') }}</p>
        @endforelse
    </div>
</div>
