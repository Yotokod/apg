<div>
    <x-page-hero :title="$lesson->tr('title')" :subtitle="$course->tr('title')" icon="fa-circle-play" />
    <x-catalog.breadcrumbs :items="[__('nav.courses') => route('courses'), $course->tr('title') => route('courses.show', $course->slug), $lesson->tr('title') => null]" />

    <div class="mx-auto grid max-w-7xl items-start gap-5 px-4 py-4 lg:grid-cols-[1fr_20rem]">
        <div class="min-w-0 space-y-4">
            @if ($lesson->motion_file)
                <x-motion :src="asset('storage/'.$lesson->motion_file)" :label="$lesson->tr('title')" />
            @endif
            @if ($lesson->motion_url)
                <div class="card overflow-hidden"><div class="aspect-video"><iframe src="{{ $lesson->motion_url }}" sandbox="allow-scripts allow-same-origin allow-presentation" referrerpolicy="strict-origin-when-cross-origin" class="h-full w-full" loading="lazy" allowfullscreen title="{{ __('catalog.lesson.motion') }}"></iframe></div></div>
            @endif
            @if ($lesson->video_url)
                <div class="card overflow-hidden"><div class="aspect-video"><iframe src="{{ $lesson->video_url }}" sandbox="allow-scripts allow-same-origin allow-presentation" referrerpolicy="strict-origin-when-cross-origin" class="h-full w-full" loading="lazy" allowfullscreen title="{{ __('catalog.lesson.video') }}"></iframe></div></div>
            @endif

            <article class="card prose max-w-none p-4 text-sm sm:p-6 sm:text-base">{!! nl2br(e($lesson->tr('content'))) !!}</article>

            @if ($lesson->nodes->isNotEmpty())
                <livewire:quiz :lesson-id="$lesson->id" :key="'quiz'.$lesson->id" />
            @endif

            <div class="flex flex-wrap items-center justify-between gap-2">
                @if ($prev) <a href="{{ route('lesson', $prev->id) }}" wire:navigate class="btn-ghost"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('courses.prev') }}</a> @else <span></span> @endif
                <button type="button" wire:click="complete" wire:loading.attr="disabled" class="btn-accent">
                    <i class="fa-solid {{ in_array($lesson->id, $done) ? 'fa-circle-check text-emerald-500' : 'fa-check' }}" aria-hidden="true"></i> {{ __('lesson.complete') }}
                </button>
                @if ($next) <a href="{{ route('lesson', $next->id) }}" wire:navigate class="btn-primary">{{ __('courses.next') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a> @else <span></span> @endif
            </div>
        </div>

        {{-- Course outline --}}
        <aside class="card space-y-3 p-3 lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto" aria-label="{{ __('catalog.lesson.outline') }}">
            <x-catalog.progress :value="count($done)" :total="$total" :label="__('catalog.courses.progress')" />
            <p class="text-xs text-stone-600 dark:text-stone-400">{{ __('catalog.lesson.position', ['n' => $position, 'total' => $total]) }}</p>
            @foreach ($modules as $m)
                <div x-data="{ open: {{ $m->lessons->contains('id', $lesson->id) ? 'true' : 'false' }} }">
                    <button type="button" @click="open = ! open" :aria-expanded="open" class="flex min-h-11 w-full items-center justify-between gap-2 text-left text-sm font-bold">
                        <span class="truncate">{{ $loop->iteration }}. {{ $m->tr('title') }}</span><i class="fa-solid fa-chevron-down shrink-0 text-xs transition-transform" :class="open && 'rotate-180'" aria-hidden="true"></i>
                    </button>
                    <ul x-show="open" x-collapse>
                        @foreach ($m->lessons as $l)
                            <li>
                                <a href="{{ route('lesson', $l->id) }}" wire:navigate @if ($l->id === $lesson->id) aria-current="page" @endif
                                   class="flex min-h-11 items-center gap-2 rounded-lg px-2 text-sm {{ $l->id === $lesson->id ? 'bg-brand-50 font-bold text-brand-800 dark:bg-stone-800 dark:text-brand-500' : 'hover:bg-stone-100 dark:hover:bg-stone-800' }}">
                                    <i class="fa-solid {{ in_array($l->id, $done) ? 'fa-circle-check text-emerald-700' : 'fa-circle text-stone-400' }} text-xs" aria-hidden="true"></i><span class="truncate">{{ $l->tr('title') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </aside>
    </div>
</div>
