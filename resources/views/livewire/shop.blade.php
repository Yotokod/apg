<div>
    <x-page-hero :title="__('nav.shop')" :subtitle="__('shop.subtitle')" icon="fa-store" />
    <div class="mx-auto max-w-7xl px-4 py-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row">
            <input type="search" wire:model.live.debounce.300ms="search" class="input sm:max-w-xs" placeholder="{{ __('shop.search') }}" aria-label="{{ __('shop.search') }}">
            <div class="flex gap-2 overflow-x-auto pb-1 sm:flex-wrap">
                <button wire:click="$set('category','')" class="btn {{ $category === '' ? 'bg-brand-600 text-white' : 'btn-ghost' }}">{{ __('shop.all') }}</button>
                @foreach ($categories as $c)
                    <button wire:click="$set('category','{{ $c }}')" class="btn {{ $category === $c ? 'bg-brand-600 text-white' : 'btn-ghost' }}">{{ trans()->has('shop.cat.'.$c) ? __('shop.cat.'.$c) : $c }}</button>
                @endforeach
            </div>
        </div>

        <div wire:loading class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @for ($i = 0; $i < 4; $i++) <div class="skeleton h-64"></div> @endfor
        </div>
        <div wire:loading.remove class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $p)
                <article class="card flex flex-col overflow-hidden">
                    <div class="flex h-40 items-center justify-center bg-gradient-to-br from-brand-100 to-amber-100 text-6xl text-brand-600 dark:from-stone-800 dark:to-stone-700">
                        @if ($p->image) <img src="{{ asset('storage/'.$p->image) }}" alt="{{ $p->tr('name') }}" loading="lazy" class="h-full w-full object-cover">
                        @else <i class="fa-solid {{ category_icon($p->category) }}"></i> @endif
                    </div>
                    <div class="flex flex-1 flex-col gap-2 p-4">
                        <span class="text-xs font-semibold uppercase text-brand-700 dark:text-brand-500">{{ trans()->has('shop.cat.'.$p->category) ? __('shop.cat.'.$p->category) : $p->category }}</span>
                        <h3 class="line-clamp-2 min-h-[2.75rem] font-bold leading-snug">{{ $p->tr('name') }}</h3>
                        <p class="line-clamp-2 text-sm text-stone-600 dark:text-stone-400">{{ $p->tr('description') }}</p>
                        <div class="mt-auto flex items-center justify-between pt-2">
                            <span class="font-extrabold">{{ fcfa($p->price) }}</span>
                            <button wire:click="add({{ $p->id }})" wire:loading.attr="disabled" class="btn-primary !px-3" aria-label="{{ __('shop.add') }}"><i class="fa-solid fa-cart-plus"></i></button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="col-span-full py-12 text-center text-stone-500">{{ __('shop.empty') }}</p>
            @endforelse
        </div>
    </div>
</div>
