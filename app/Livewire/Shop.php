<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\CartStore;
use App\Support\Catalog;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Shop extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;
    public const SORTS = ['newest', 'price_asc', 'price_desc'];

    #[Url(as: 'cat')]
    public string $category = '';

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'min')]
    public string $minPrice = '';

    #[Url(as: 'max')]
    public string $maxPrice = '';

    #[Url(as: 'stock')]
    public bool $inStock = false;

    #[Url]
    public string $sort = 'newest';

    #[Url]
    public string $view = 'grid';

    public function updated(string $name): void
    {
        if (! in_array($name, ['view', 'page'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('category', 'search', 'minPrice', 'maxPrice', 'inStock');
        $this->resetPage();
    }

    public function add(int $id): void
    {
        $ok = CartStore::add($id);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: $ok ? __('shop.added') : __('catalog.shop.unavailable'));
    }

    private function query()
    {
        $min = $this->minPrice !== '' ? max(0, (int) $this->minPrice) : null;
        $max = $this->maxPrice !== '' ? max(0, (int) $this->maxPrice) : null;

        $query = Product::where('is_active', true)
            ->when($this->category !== '', fn ($q) => $q->where('category', $this->category))
            ->tap(fn ($q) => Catalog::whereTranslatedLike($q, ['name'], $this->search))
            ->when($min !== null, fn ($q) => $q->where('price', '>=', $min))
            ->when($max !== null, fn ($q) => $q->where('price', '<=', $max))
            ->when($this->inStock, fn ($q) => $q->where('stock', '>', 0));

        return match ($this->sort) {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };
    }

    public function render()
    {
        if (! in_array($this->sort, self::SORTS, true)) {
            $this->sort = 'newest';
        }
        $this->view = $this->view === 'list' ? 'list' : 'grid';
        $active = Product::where('is_active', true);

        return view('livewire.shop', [
            'products' => $this->query()->paginate(self::PER_PAGE),
            'categories' => (clone $active)->distinct()->orderBy('category')->pluck('category'),
            'priceMax' => (int) (clone $active)->max('price'),
            'sold' => Catalog::soldUnits(),
            'activeFilters' => collect([$this->category, $this->search, $this->minPrice, $this->maxPrice, $this->inStock ?: ''])->filter(fn ($v) => $v !== '')->count(),
        ])->layout('components.layouts.app', ['title' => __('nav.shop').' | '.site('name')]);
    }
}
