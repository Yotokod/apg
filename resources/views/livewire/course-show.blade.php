<div>
    <x-page-hero :title="$course->tr('title')" :subtitle="$course->tr('description')" icon="fa-book-open-reader" />
    <div class="mx-auto max-w-4xl space-y-4 px-4 py-8">
        @if (! $enrolled)
            <div class="card flex flex-col items-center justify-between gap-3 p-5 sm:flex-row">
                <p class="font-semibold">{{ $course->price ? fcfa($course->price) : __('courses.free') }}</p>
                <button wire:click="enroll" class="btn-primary"><i class="fa-solid fa-unlock"></i> {{ __('courses.enroll') }}</button>
            </div>
        @endif
        @foreach ($course->modules as $m)
            <section class="card p-4" x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
                <button class="flex w-full items-center justify-between text-left font-bold" @click="open = !open" :aria-expanded="open">
                    <span class="truncate">{{ $loop->iteration }}. {{ $m->tr('title') }}</span><i class="fa-solid fa-chevron-down" :class="open && 'rotate-180'"></i>
                </button>
                <ul x-show="open" x-collapse class="mt-3 space-y-1">
                    @foreach ($m->lessons as $l)
                        <li>
                            @if ($enrolled)
                                <a href="{{ route('lesson', $l->id) }}" wire:navigate class="flex items-center gap-2 rounded-xl px-3 py-2 hover:bg-stone-100 dark:hover:bg-stone-800">
                                    <i class="fa-solid {{ in_array($l->id, $done) ? 'fa-circle-check text-brand-600' : 'fa-circle-play text-stone-400' }}"></i><span class="truncate">{{ $l->tr('title') }}</span>
                                </a>
                            @else
                                <span class="flex items-center gap-2 px-3 py-2 text-stone-500"><i class="fa-solid fa-lock"></i><span class="truncate">{{ $l->tr('title') }}</span></span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</div>
