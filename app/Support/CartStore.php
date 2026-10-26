<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/** Session cart: [product_id => qty], quantities clamped to 1..MAX_QTY, unknown/inactive ids ignored. */
class CartStore
{
    public const MAX_QTY = 99;

    public static function clamp(int $qty): int
    {
        return max(1, min(self::MAX_QTY, $qty));
    }

    /** @return array<int,int> */
    public static function raw(): array
    {
        return array_filter(array_map('intval', session('cart', [])), fn ($q) => $q > 0);
    }

    public static function add(int $id, int $qty = 1): bool
    {
        $product = Product::where('is_active', true)->find($id);
        if (! $product || $product->stock < 1) {
            return false;
        }
        $cart = self::raw();
        $cart[$id] = self::clamp(($cart[$id] ?? 0) + $qty);
        session(['cart' => $cart]);

        return true;
    }

    public static function set(int $id, int $qty): void
    {
        $cart = self::raw();
        if (! isset($cart[$id])) {
            return;
        }
        $cart[$id] = self::clamp($qty);
        session(['cart' => $cart]);
    }

    public static function remove(int $id): void
    {
        $cart = self::raw();
        unset($cart[$id]);
        session(['cart' => $cart]);
    }

    /** @return Collection<int,array{product:Product,qty:int}> */
    public static function lines(): Collection
    {
        $cart = self::raw();
        $lines = Product::where('is_active', true)->whereIn('id', array_keys($cart))->get()
            ->map(fn (Product $p) => ['product' => $p, 'qty' => self::clamp($cart[$p->id])])
            ->values();
        $valid = $lines->mapWithKeys(fn ($l) => [$l['product']->id => $l['qty']])->all();
        if ($valid !== $cart) {
            session(['cart' => $valid]);
        }

        return $lines;
    }

    public static function total(Collection $lines): int
    {
        return (int) $lines->sum(fn ($l) => $l['product']->price * $l['qty']);
    }
}
