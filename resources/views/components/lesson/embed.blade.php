{{-- External embed (motion design / video) in a sandboxed iframe. --}}
@props(['src', 'title'])
<div class="card overflow-hidden">
    <div class="aspect-video bg-stone-100 dark:bg-stone-800">
        <iframe src="{{ $src }}" sandbox="allow-scripts allow-same-origin allow-presentation" referrerpolicy="strict-origin-when-cross-origin"
                class="h-full w-full" loading="lazy" allowfullscreen title="{{ $title }}"></iframe>
    </div>
</div>
