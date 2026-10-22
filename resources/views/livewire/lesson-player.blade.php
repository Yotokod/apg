<div>
    <x-page-hero :title="$lesson->tr('title')" :subtitle="$lesson->module->course->tr('title')" icon="fa-circle-play" />
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-8">
        <a href="{{ route('courses.show', $lesson->module->course->slug) }}" wire:navigate class="text-sm text-brand-700"><i class="fa-solid fa-arrow-left"></i> {{ $lesson->module->course->tr('title') }}</a>

        @if ($lesson->motion_url)
            <div class="card overflow-hidden"><div class="aspect-video"><iframe src="{{ $lesson->motion_url }}" class="h-full w-full" loading="lazy" allowfullscreen title="Motion"></iframe></div></div>
        @endif
        @if ($lesson->video_url)
            <div class="card overflow-hidden"><div class="aspect-video"><iframe src="{{ $lesson->video_url }}" class="h-full w-full" loading="lazy" allowfullscreen title="Video"></iframe></div></div>
        @endif

        <article class="card prose max-w-none p-6 dark:prose-invert">{!! nl2br(e($lesson->tr('content'))) !!}</article>

        @if ($lesson->nodes->isNotEmpty())
            <livewire:quiz :lesson-id="$lesson->id" :key="'quiz'.$lesson->id" />
        @endif

        <div class="flex justify-between">
            @if ($prev) <a href="{{ route('lesson', $prev->id) }}" wire:navigate class="btn-ghost"><i class="fa-solid fa-arrow-left"></i> {{ __('courses.prev') }}</a> @else <span></span> @endif
            @if ($next) <a href="{{ route('lesson', $next->id) }}" wire:navigate class="btn-primary">{{ __('courses.next') }} <i class="fa-solid fa-arrow-right"></i></a> @endif
        </div>
    </div>
</div>
