@props(['current' => 1])
<ol class="mb-5 flex items-center gap-2 text-sm" aria-label="{{ __('catalog.cart.steps') }}">
    @foreach (['cart', 'info', 'payment'] as $i => $key)
        @php($n = $i + 1)
        <li class="flex flex-1 items-center gap-2 {{ $loop->last ? 'flex-none' : '' }}" @if ($n === $current) aria-current="step" @endif>
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-bold {{ $n <= $current ? 'bg-brand-600 text-white' : 'bg-stone-200 text-stone-700 dark:bg-stone-800 dark:text-stone-300' }}">
                @if ($n < $current)<i class="fa-solid fa-check" aria-hidden="true"></i>@else{{ $n }}@endif
            </span>
            <span class="truncate font-semibold {{ $n === $current ? '' : 'text-stone-600 dark:text-stone-400' }}">{{ __('catalog.cart.step_'.$key) }}</span>
            @unless ($loop->last)<span class="h-px flex-1 bg-stone-300 dark:bg-stone-700" aria-hidden="true"></span>@endunless
        </li>
    @endforeach
</ol>
