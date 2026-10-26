<div>
    <x-page-hero :title="__('nav.cart')" :subtitle="$lines->isNotEmpty() ? trans_choice('catalog.cart.items', $count, ['count' => $count]) : null" icon="fa-basket-shopping" />
    <x-catalog.breadcrumbs :items="[__('nav.shop') => route('shop'), __('nav.cart') => null]" />
    <div class="mx-auto max-w-6xl px-4 py-4">
        @if ($order)
            <x-catalog.stepper :current="3" />
            <div class="card space-y-4 p-6">
                <h2 class="text-xl font-extrabold text-brand-700"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> {{ __('cart.thanks', ['id' => $order->id]) }}</h2>
                <p>{{ __('cart.total') }}: <strong>{{ fcfa($order->total) }}</strong></p>
                @if ($chosen?->type === 'transfer')
                    <div class="rounded-xl bg-brand-50 p-4 text-sm dark:bg-stone-800">
                        <p class="whitespace-pre-line">{{ $chosen->instructions[app()->getLocale()] ?? $chosen->instructions['fr'] ?? __('cart.transfer_default') }}</p>
                        <a class="btn-accent mt-3" target="_blank" rel="noopener"
                           href="https://www.openstreetmap.org/search?query={{ urlencode($chosen->name.' '.$order->city) }}">
                            <i class="fa-solid fa-map-location-dot" aria-hidden="true"></i> {{ __('cart.find_agent') }}
                        </a>
                    </div>
                @endif
                <a href="{{ route('shop') }}" wire:navigate class="btn-ghost">{{ __('cart.continue') }}</a>
            </div>
        @elseif ($lines->isEmpty())
            <div class="card flex flex-col items-center gap-2 p-10 text-center">
                <i class="fa-solid fa-basket-shopping text-5xl text-stone-400" aria-hidden="true"></i>
                <p class="font-semibold">{{ __('cart.empty') }}</p>
                <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('catalog.cart.empty_hint') }}</p>
                <a href="{{ route('shop') }}" wire:navigate class="btn-primary mt-2">{{ __('nav.shop') }}</a>
            </div>
        @else
            <x-catalog.stepper :current="$step" />
            <div class="grid items-start gap-5 lg:grid-cols-[1fr_22rem]">
                <div>
                    {{-- Step 1: lines --}}
                    @if ($step === 1)
                        <ul class="card divide-y divide-stone-200 dark:divide-stone-800">
                            @foreach ($lines as $l)
                                @php($p = $l['product'])
                                <li class="flex items-center gap-3 p-3" wire:key="line{{ $p->id }}">
                                    <a href="{{ route('product.show', $p->slug) }}" wire:navigate class="block size-16 shrink-0 overflow-hidden rounded-lg" tabindex="-1" aria-hidden="true">
                                        <x-catalog.product-image :product="$p" size="text-2xl" class="size-16" />
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('product.show', $p->slug) }}" wire:navigate class="line-clamp-2 text-sm font-bold hover:text-brand-700">{{ $p->tr('name') }}</a>
                                        <p class="text-xs text-stone-600 dark:text-stone-400">{{ fcfa($p->price) }}</p>
                                        <div class="mt-1 inline-flex items-center overflow-hidden rounded-xl border border-stone-300 dark:border-stone-700" role="group" aria-label="{{ __('catalog.product.qty') }}">
                                            <button type="button" wire:click="qty({{ $p->id }}, -1)" class="flex size-11 items-center justify-center hover:bg-stone-100 dark:hover:bg-stone-800" aria-label="{{ __('catalog.cart.decrease') }}"><i class="fa-solid fa-minus" aria-hidden="true"></i></button>
                                            <span class="w-9 text-center text-sm font-bold" aria-live="polite">{{ $l['qty'] }}</span>
                                            <button type="button" wire:click="qty({{ $p->id }}, 1)" @disabled($l['qty'] >= \App\Support\CartStore::MAX_QTY) class="flex size-11 items-center justify-center hover:bg-stone-100 disabled:opacity-40 dark:hover:bg-stone-800" aria-label="{{ __('catalog.cart.increase') }}"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                                        </div>
                                    </div>
                                    <div class="flex flex-col items-end gap-1">
                                        <p class="font-extrabold">{{ fcfa($p->price * $l['qty']) }}</p>
                                        <button type="button" wire:click="remove({{ $p->id }})" class="flex size-11 items-center justify-center rounded-xl text-stone-600 hover:bg-red-50 hover:text-red-700 dark:text-stone-400 dark:hover:bg-red-950" aria-label="{{ __('catalog.cart.remove') }}: {{ $p->tr('name') }}"><i class="fa-solid fa-trash-can" aria-hidden="true"></i></button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-3 flex flex-wrap justify-between gap-2">
                            <a href="{{ route('shop') }}" wire:navigate class="btn-ghost"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('cart.continue') }}</a>
                            <button type="button" wire:click="goTo(2)" class="btn-primary">{{ __('catalog.cart.next') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                        </div>
                    @endif

                    {{-- Step 2: customer info --}}
                    @if ($step === 2)
                        <form wire:submit="goTo(3)" class="card space-y-3 p-4">
                            <h2 class="font-extrabold">{{ __('cart.your_info') }}</h2>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ([['name', 'cart.name', 'name'], ['phone', 'cart.phone', 'tel'], ['email', 'cart.email', 'email'], ['city', 'cart.city', 'address-level2']] as [$f, $k, $ac])
                                    <label class="text-sm font-medium">{{ __($k) }}
                                        <input wire:model="{{ $f }}" class="input mt-1" autocomplete="{{ $ac }}" @if ($f === 'phone') type="tel" inputmode="tel" @elseif ($f === 'email') type="email" @endif>
                                        @error($f) <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                                    </label>
                                @endforeach
                            </div>
                            <div class="flex flex-wrap justify-between gap-2 pt-1">
                                <button type="button" wire:click="goTo(1)" class="btn-ghost"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('catalog.cart.back') }}</button>
                                <button class="btn-primary">{{ __('catalog.cart.next') }} <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                            </div>
                        </form>
                    @endif

                    {{-- Step 3: payment --}}
                    @if ($step === 3)
                        <form wire:submit="checkout" class="card space-y-3 p-4">
                            <h2 class="font-extrabold">{{ __('cart.payment') }}</h2>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @forelse ($gateways as $g)
                                    <label class="card flex min-h-11 cursor-pointer items-center gap-3 p-3 {{ $gateway === $g->code ? 'ring-2 ring-brand-600' : '' }}">
                                        <input type="radio" wire:model.live="gateway" value="{{ $g->code }}" class="size-4 accent-brand-600">
                                        <i class="fa-solid {{ $g->type === 'transfer' ? 'fa-money-bill-transfer' : 'fa-credit-card' }}" aria-hidden="true"></i>
                                        <span class="truncate font-semibold">{{ $g->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-sm text-stone-600 dark:text-stone-400 sm:col-span-2">{{ __('cart.no_gateway') }}</p>
                                @endforelse
                            </div>
                            @error('gateway') <span class="text-xs text-red-700">{{ $message }}</span> @enderror
                            <div class="flex flex-wrap justify-between gap-2 pt-1">
                                <button type="button" wire:click="goTo(2)" class="btn-ghost"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> {{ __('catalog.cart.back') }}</button>
                                <button class="btn-primary" wire:loading.attr="disabled" wire:target="checkout"><span wire:loading wire:target="checkout"><i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i></span> {{ __('cart.pay') }} · {{ fcfa($total) }}</button>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Summary + trust --}}
                <aside class="space-y-3 lg:sticky lg:top-24" aria-label="{{ __('catalog.cart.summary') }}">
                    <div class="card space-y-2 p-4 text-sm">
                        <h2 class="font-extrabold">{{ __('catalog.cart.summary') }}</h2>
                        <p class="flex justify-between"><span>{{ trans_choice('catalog.cart.items', $count, ['count' => $count]) }}</span><span>{{ fcfa($total) }}</span></p>
                        <p class="flex justify-between text-stone-600 dark:text-stone-400"><span>{{ __('catalog.cart.shipping') }}</span><span>{{ __('catalog.cart.shipping_note') }}</span></p>
                        <p class="flex justify-between border-t border-stone-200 pt-2 text-lg font-extrabold dark:border-stone-800"><span>{{ __('cart.total') }}</span><span>{{ fcfa($total) }}</span></p>
                    </div>
                    <ul class="card space-y-2 p-4 text-sm">
                        @foreach (['truck-fast' => 'delivery', 'shield-halved' => 'secure', 'fa-brands fa-whatsapp' => 'support'] as $icon => $key)
                            <li class="flex items-start gap-3"><i class="{{ str_starts_with($icon, 'fa-brands') ? $icon : 'fa-solid fa-'.$icon }} mt-0.5 w-5 text-center text-brand-600" aria-hidden="true"></i><span>{{ __('catalog.cart.trust_'.$key) }}</span></li>
                        @endforeach
                    </ul>
                </aside>
            </div>
        @endif
    </div>
</div>
