<div data-stagger class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
    @foreach ([['fa-truck-fast', 'home.f1', 'home.f1_d'], ['fa-shield-heart', 'home.f2', 'home.f2_d'], ['fa-graduation-cap', 'home.f3', 'home.f3_d'], ['fa-wallet', 'home.t4', 'home.t4_d']] as [$i, $t, $d])
        <div class="card flex items-center gap-3 p-3">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-lg text-brand-600 dark:bg-stone-800" aria-hidden="true"><i class="fa-solid {{ $i }}"></i></span>
            <div class="min-w-0"><p class="truncate text-sm font-bold">{{ __($t) }}</p><p class="line-clamp-2 text-xs text-stone-600 dark:text-stone-400">{{ __($d) }}</p></div>
        </div>
    @endforeach
</div>
