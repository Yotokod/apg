<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\CartStore;
use App\Support\Catalog;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;

    public int $qty = 1;

    public function mount(string $slug): void
    {
        $this->product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
    }

    public function updatedQty(): void
    {
        $this->qty = CartStore::clamp($this->qty);
    }

    public function step(int $delta): void
    {
        $this->qty = CartStore::clamp($this->qty + $delta);
    }

    public function add(?int $id = null): void
    {
        $id ??= $this->product->id;
        $ok = CartStore::add($id, $id === $this->product->id ? CartStore::clamp($this->qty) : 1);
        $this->dispatch('cart-updated');
        $this->dispatch('toast', message: $ok ? __('shop.added') : __('catalog.shop.unavailable'));
    }

    private function jsonLd(Product $p): string
    {
        $data = array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $p->tr('name'),
            'description' => $p->tr('description'),
            'sku' => (string) $p->id,
            'category' => Catalog::categoryLabel($p->category),
            'image' => Catalog::imageUrl($p->image),
            'url' => route('product.show', $p->slug),
            'brand' => ['@type' => 'Brand', 'name' => site('name')],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('product.show', $p->slug),
                'price' => (string) $p->price,
                'priceCurrency' => 'XOF',
                'availability' => 'https://schema.org/'.($p->stock > 0 ? 'InStock' : 'OutOfStock'),
            ],
        ], fn ($v) => $v !== null && $v !== '');

        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    public function render()
    {
        $p = $this->product;
        $message = __('catalog.product.wa_message', ['name' => $p->tr('name'), 'qty' => $this->qty, 'url' => route('product.show', $p->slug)]);

        return view('livewire.product-show', [
            'related' => Product::where('is_active', true)->where('category', $p->category)->whereKeyNot($p->id)->latest()->limit(4)->get(),
            'sold' => Catalog::soldUnits(),
            'payments' => Catalog::paymentMethods(),
            'jsonLd' => $this->jsonLd($p),
            'whatsapp' => Catalog::whatsappUrl($message),
        ])->layout('components.layouts.app', ['title' => $p->tr('name').' | '.site('name')]);
    }
}
