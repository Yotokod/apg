@props(['title', 'subtitle' => null, 'icon' => 'fa-leaf'])
<section class="hero-sm mt-3">
    <i class="fa-solid {{ $icon }} pointer-events-none absolute -right-4 -top-4 text-[9rem] opacity-10" aria-hidden="true"></i>
    <div class="relative mx-auto w-full max-w-7xl px-4 py-3">
        <h1 class="truncate text-xl font-extrabold sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle) <p class="truncate text-sm text-white/80">{{ $subtitle }}</p> @endif
    </div>
</section>
