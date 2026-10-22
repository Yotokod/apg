<div>
    <x-page-hero :title="__('nav.cart')" icon="fa-basket-shopping" />
    <div class="mx-auto max-w-5xl px-4 py-8">
        @if ($order)
            <div class="card space-y-4 p-6">
                <h2 class="text-xl font-extrabold text-brand-700"><i class="fa-solid fa-circle-check"></i> {{ __('cart.thanks', ['id' => $order->id]) }}</h2>
                <p>{{ __('cart.total') }}: <strong>{{ fcfa($order->total) }}</strong></p>
                @if ($chosen?->type === 'transfer')
                    <div class="rounded-xl bg-amber-50 p-4 text-sm dark:bg-stone-800">
                        <p class="whitespace-pre-line">{{ $chosen->instructions[app()->getLocale()] ?? $chosen->instructions['fr'] ?? __('cart.transfer_default') }}</p>
                        <a class="btn-accent mt-3" target="_blank" rel="noopener"
                           href="https://www.openstreetmap.org/search?query={{ urlencode($chosen->name.' '.$order->city) }}">
                            <i class="fa-solid fa-map-location-dot"></i> {{ __('cart.find_agent') }}
                        </a>
                    </div>
                @endif
                <a href="{{ route('shop') }}" wire:navigate class="btn-ghost">{{ __('cart.continue') }}</a>
            </div>
        @elseif ($lines->isEmpty())
            <div class="card p-10 text-center">
                <i class="fa-solid fa-basket-shopping text-5xl text-stone-300"></i>
                <p class="mt-3 text-stone-500">{{ __('cart.empty') }}</p>
                <a href="{{ route('shop') }}" wire:navigate class="btn-primary mt-4">{{ __('nav.shop') }}</a>
            </div>
        @else
            <div class="grid gap-6 lg:grid-cols-5">
                <div class="card divide-y divide-stone-200 p-2 dark:divide-stone-800 lg:col-span-2">
                    @foreach ($lines as $l)
                        <div class="flex items-center justify-between gap-2 p-3">
                            <div class="min-w-0"><p class="truncate font-semibold">{{ $l['product']->tr('name') }}</p><p class="text-sm text-stone-500">{{ fcfa($l['product']->price) }}</p></div>
                            <div class="flex items-center gap-2">
                                <button wire:click="qty({{ $l['product']->id }}, -1)" class="btn-ghost !min-h-9 !px-3" aria-label="-">−</button>
                                <span class="w-6 text-center">{{ $l['qty'] }}</span>
                                <button wire:click="qty({{ $l['product']->id }}, 1)" class="btn-ghost !min-h-9 !px-3" aria-label="+">+</button>
                            </div>
                        </div>
                    @endforeach
                    <div class="flex justify-between p-3 text-lg font-extrabold"><span>{{ __('cart.total') }}</span><span>{{ fcfa($total) }}</span></div>
                </div>
                <form wire:submit="checkout" class="card space-y-3 p-5 lg:col-span-3">
                    <h2 class="font-extrabold">{{ __('cart.your_info') }}</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ([['name', 'cart.name'], ['phone', 'cart.phone'], ['email', 'cart.email'], ['city', 'cart.city']] as [$f, $k])
                            <label class="text-sm font-medium">{{ __($k) }}
                                <input wire:model="{{ $f }}" class="input mt-1" @if ($f === 'phone') type="tel" @endif>
                                @error($f) <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                        @endforeach
                    </div>
                    <h2 class="pt-2 font-extrabold">{{ __('cart.payment') }}</h2>
                    <div class="grid gap-2 sm:grid-cols-2">
                        @forelse ($gateways as $g)
                            <label class="card flex cursor-pointer items-center gap-3 p-3 {{ $gateway === $g->code ? 'ring-2 ring-brand-600' : '' }}">
                                <input type="radio" wire:model.live="gateway" value="{{ $g->code }}" class="accent-brand-600">
                                <i class="fa-solid {{ $g->type === 'transfer' ? 'fa-money-bill-transfer' : 'fa-credit-card' }}"></i>
                                <span class="truncate font-semibold">{{ $g->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-stone-500 sm:col-span-2">{{ __('cart.no_gateway') }}</p>
                        @endforelse
                    </div>
                    @error('gateway') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    <button class="btn-primary w-full" wire:loading.attr="disabled"><span wire:loading wire:target="checkout"><i class="fa-solid fa-spinner fa-spin"></i></span> {{ __('cart.pay') }} · {{ fcfa($total) }}</button>
                </form>
            </div>
        @endif
    </div>
</div>
