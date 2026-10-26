@props(['categories'])
<div data-stagger class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    @foreach ($categories as $c)
        <a href="{{ route('shop', ['cat' => $c['key']]) }}" wire:navigate class="card group flex flex-col items-center gap-2 p-4 text-center">
            <span class="relative flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl bg-brand-50 text-2xl text-brand-600 transition-colors group-hover:bg-brand-600 group-hover:text-white dark:bg-stone-800">
                @if ($c['image'])<img src="{{ $c['image'] }}" alt="" loading="lazy" decoding="async" width="64" height="64" class="h-full w-full object-cover">
                @else<i class="fa-solid {{ category_icon($c['key']) }}" aria-hidden="true"></i>@endif
            </span>
            <span class="w-full truncate text-sm font-bold">{{ $c['label'] }}</span>
            <span class="text-xs text-stone-600 dark:text-stone-400">{{ trans_choice('home.n_products', $c['count'], ['count' => $c['count']]) }}</span>
        </a>
    @endforeach
</div>
