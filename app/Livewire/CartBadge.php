<?php

namespace App\Livewire;

use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    public int $n = 0;

    public function mount(): void
    {
        $this->refresh();
    }

    #[On('cart-updated')]
    public function refresh(): void
    {
        $this->n = array_sum(session('cart', []));
    }

    public function render()
    {
        return <<<'HTML'
        <span>@if ($n) <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-accent-500 px-1 text-xs font-bold text-stone-900">{{ $n }}</span> @endif</span>
        HTML;
    }
}
