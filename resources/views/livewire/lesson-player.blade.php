<div x-data="{ outline: false }" @keydown.escape.window="outline = false">
    <x-page-hero :title="$lesson->tr('title')" :subtitle="$course->tr('title')" icon="fa-circle-play" />
    <x-catalog.breadcrumbs :items="[__('nav.courses') => route('courses'), $course->tr('title') => route('courses.show', $course->slug), $lesson->tr('title') => null]" />
    @php $content = trim((string) $lesson->tr('content')); @endphp

    <div class="mx-auto grid max-w-7xl items-start gap-5 px-4 py-4 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-4" aria-label="{{ __('lesson.workspace') }}" role="region">
            {{-- Mobile: outline drawer toggle + position --}}
            <div class="flex items-center justify-between gap-2 lg:hidden">
                <button type="button" @click="outline = true; $nextTick(() => $refs.closeOutline.focus())" :aria-expanded="outline" aria-controls="lesson-outline" class="btn-ghost !px-3">
                    <i class="fa-solid fa-list-ul" aria-hidden="true"></i> {{ __('lesson.outline_open') }}
                </button>
                <span class="truncate text-xs font-semibold text-stone-600 dark:text-stone-400">{{ __('catalog.lesson.position', ['n' => $position, 'total' => $total]) }}</span>
            </div>

            {{-- 1. Animation first --}}
            @if ($scenes || $lesson->motion_file || $lesson->motion_url || $lesson->video_url)
                <div class="space-y-4" aria-label="{{ __('lesson.animation') }}" role="region">
                    @if ($scenes)
                        <x-lesson.explainer :scenes="$scenes" :title="$lesson->tr('title')" />
                    @endif
                    @if ($lesson->motion_file)
                        <x-lesson.lottie :src="asset('storage/'.$lesson->motion_file)" :label="$lesson->tr('title')" :transcript="$content ?: null" />
                    @endif
                    @if ($lesson->motion_url)
                        <x-lesson.embed :src="$lesson->motion_url" :title="__('catalog.lesson.motion')" />
                    @endif
                    @if ($lesson->video_url)
                        <x-lesson.embed :src="$lesson->video_url" :title="__('catalog.lesson.video')" />
                    @endif
                </div>
            @endif

            {{-- 2. Text --}}
            @if ($content !== '')
                <article class="card prose max-w-none p-4 text-sm sm:p-6 sm:text-base" aria-label="{{ __('lesson.content') }}">{!! nl2br(e($content)) !!}</article>
            @endif

            {{-- 3. Quiz --}}
            @if ($lesson->nodes->isNotEmpty())
                <livewire:quiz :lesson-id="$lesson->id" :key="'quiz'.$lesson->id" />
            @endif

            {{-- 4. Navigation --}}
            <nav class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:justify-between" aria-label="{{ __('lesson.nav') }}">
                @if ($prev) <a href="{{ route('lesson', $prev->id) }}" wire:navigate class="btn-ghost min-w-0"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> <span class="truncate">{{ __('courses.prev') }}</span></a> @else <span class="hidden sm:block"></span> @endif
                @if ($next) <a href="{{ route('lesson', $next->id) }}" wire:navigate class="btn-primary min-w-0 sm:order-last"><span class="truncate">{{ __('courses.next') }}</span> <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a> @endif
                <button type="button" wire:click="complete" wire:loading.attr="disabled" class="btn-accent col-span-2 sm:col-span-1">
                    <i class="fa-solid {{ in_array($lesson->id, $done) ? 'fa-circle-check text-emerald-500' : 'fa-check' }}" aria-hidden="true"></i> {{ __('lesson.complete') }}
                </button>
            </nav>
        </div>

        {{-- Course outline: sticky column on desktop, drawer on mobile --}}
        <div x-show="outline" x-transition.opacity @click="outline = false" class="fixed inset-0 z-40 bg-ink-900/50 lg:hidden" style="display: none" aria-hidden="true"></div>
        <aside id="lesson-outline" :class="outline ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="card fixed inset-y-0 left-0 z-50 w-[min(20rem,86vw)] -translate-x-full space-y-3 overflow-y-auto rounded-none p-3 transition-transform duration-300 lg:sticky lg:inset-auto lg:top-24 lg:z-auto lg:max-h-[calc(100vh-7rem)] lg:w-auto lg:translate-x-0 lg:rounded-2xl"
               aria-label="{{ __('catalog.lesson.outline') }}">
            <div class="flex items-center justify-between gap-2 lg:hidden">
                <p class="font-extrabold">{{ __('catalog.lesson.outline') }}</p>
                <button type="button" x-ref="closeOutline" @click="outline = false" class="btn-ghost !px-3" aria-label="{{ __('lesson.outline_close') }}"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
            </div>
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
                                    <i class="fa-solid {{ in_array($l->id, $done) ? 'fa-circle-check text-emerald-700' : 'fa-circle text-stone-400' }} text-xs" aria-hidden="true"></i>
                                    @if ($l->scenes) <i class="fa-solid fa-film text-xs text-brand-600" aria-hidden="true"></i> @endif
                                    <span class="truncate">{{ $l->tr('title') }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </aside>
    </div>
</div>
