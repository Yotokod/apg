<div>
    <x-page-hero :title="__('nav.account')" :subtitle="auth()->user()->name" icon="fa-user" />
    <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 md:grid-cols-2">
        @if (session('status') === 'payment_pending')
            <p class="card md:col-span-2 bg-brand-50 p-4 text-sm font-medium dark:bg-stone-800"><i class="fa-solid fa-hourglass-half text-accent-500"></i> {{ __('account.payment_pending') }}</p>
        @endif
        <section class="card p-5"><h2 class="mb-3 font-extrabold">{{ __('nav.courses') }}</h2>
            @forelse ($enrollments as $e)
                <a href="{{ route('courses.show', $e->course->slug) }}" wire:navigate class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-stone-100 dark:hover:bg-stone-800"><span class="truncate">{{ $e->course->tr('title') }}</span><i class="fa-solid {{ $e->paid ? 'fa-unlock text-brand-600' : 'fa-clock text-stone-400' }}"></i></a>
            @empty <p class="text-sm text-stone-500">{{ __('courses.empty') }}</p> @endforelse
        </section>
        <section class="card p-5"><h2 class="mb-3 flex items-center gap-2 font-extrabold"><i class="fa-solid fa-award text-brand-600" aria-hidden="true"></i> {{ __('certificate.mine') }}</h2>
            @forelse ($certificates as $c)
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl px-3 py-2 hover:bg-stone-100 dark:hover:bg-stone-800">
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $c->tr('course_title') }}</p>
                        <p class="truncate text-xs text-stone-600 dark:text-stone-400">{{ $c->number }} · {{ $c->issued_at->locale(app()->getLocale())->isoFormat('LL') }} · <span class="{{ $c->isRevoked() ? 'text-red-700' : 'text-emerald-700' }} font-semibold">{{ __($c->isRevoked() ? 'certificate.revoked' : 'certificate.valid') }}</span></p>
                    </div>
                    <a href="{{ $c->url() }}" target="_blank" rel="noopener" class="btn-ghost shrink-0"><i class="fa-solid fa-print" aria-hidden="true"></i> {{ __('certificate.view') }}</a>
                </div>
            @empty <p class="text-sm text-stone-600 dark:text-stone-400">{{ __('certificate.none') }}</p> @endforelse
        </section>
        <section class="card p-5"><h2 class="mb-3 font-extrabold">{{ __('account.orders') }}</h2>
            @forelse ($orders as $o)
                <p class="flex justify-between px-3 py-2 text-sm"><span>#{{ $o->id }} · {{ __('status.'.$o->status) }}</span><strong>{{ fcfa($o->total) }}</strong></p>
            @empty <p class="text-sm text-stone-500">{{ __('shop.empty') }}</p> @endforelse
        </section>
        <button wire:click="logout" class="btn-ghost w-fit"><i class="fa-solid fa-right-from-bracket"></i> {{ __('account.logout') }}</button>
    </div>
</div>
