@props(['items', 'dark' => false])
<nav aria-label="{{ __('catalog.breadcrumb') }}" {{ $attributes->class(['text-xs', $dark ? 'text-white/85' : 'mx-auto max-w-7xl px-4 pt-3 text-stone-600 dark:text-stone-400']) }}>
    <ol class="flex flex-wrap items-center gap-1.5">
        <li><a href="{{ route('home') }}" wire:navigate class="inline-flex min-h-6 items-center hover:underline"><i class="fa-solid fa-house" aria-hidden="true"></i><span class="sr-only">{{ __('nav.home') }}</span></a></li>
        @foreach ($items as $label => $url)
            <li aria-hidden="true"><i class="fa-solid fa-chevron-right text-[0.6rem]"></i></li>
            <li class="min-w-0 max-w-[14rem] truncate sm:max-w-xs">
                @if ($url) <a href="{{ $url }}" wire:navigate class="hover:underline">{{ $label }}</a>
                @else <span aria-current="page" class="font-semibold {{ $dark ? 'text-white' : 'text-ink-900 dark:text-stone-100' }}">{{ $label }}</span> @endif
            </li>
        @endforeach
    </ol>
</nav>
