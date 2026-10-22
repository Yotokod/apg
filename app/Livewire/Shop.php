<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;

class Shop extends Component
{
    #[Url(as: 'cat')]
    public string $category = '';

    #[Url(as: 'q')]
    public string $search = '';

    public function add(int $id): void
    {
        $cart = session('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        session(['cart' => $cart]);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: __('shop.added'));
    }

    public function render()
    {
        $products = Product::where('is_active', true)
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->get();

        return view('livewire.shop', [
            'products' => $products,
            'categories' => Product::where('is_active', true)->distinct()->pluck('category'),
        ])->layout('components.layouts.app', ['title' => __('nav.shop').' | '.site('name')]);
    }
}
