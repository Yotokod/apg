<div>
    <x-page-hero :title="__('nav.account')" :subtitle="auth()->user()->name" icon="fa-user" />
    <div class="mx-auto grid max-w-5xl gap-6 px-4 py-8 md:grid-cols-2">
        <section class="card p-5"><h2 class="mb-3 font-extrabold">{{ __('nav.courses') }}</h2>
            @forelse ($enrollments as $e)
                <a href="{{ route('courses.show', $e->course->slug) }}" wire:navigate class="flex items-center justify-between rounded-xl px-3 py-2 hover:bg-stone-100 dark:hover:bg-stone-800"><span class="truncate">{{ $e->course->tr('title') }}</span><i class="fa-solid {{ $e->paid ? 'fa-unlock text-brand-600' : 'fa-clock text-stone-400' }}"></i></a>
            @empty <p class="text-sm text-stone-500">{{ __('courses.empty') }}</p> @endforelse
        </section>
        <section class="card p-5"><h2 class="mb-3 font-extrabold">{{ __('account.orders') }}</h2>
            @forelse ($orders as $o)
                <p class="flex justify-between px-3 py-2 text-sm"><span>#{{ $o->id }} · {{ __('status.'.$o->status) }}</span><strong>{{ fcfa($o->total) }}</strong></p>
            @empty <p class="text-sm text-stone-500">{{ __('shop.empty') }}</p> @endforelse
        </section>
        <button wire:click="logout" class="btn-ghost w-fit"><i class="fa-solid fa-right-from-bracket"></i> {{ __('account.logout') }}</button>
    </div>
</div>
