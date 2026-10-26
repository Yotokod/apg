<?php

namespace App\Support;

use App\Models\PaymentGateway;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Presentation helpers shared by the shop views. */
class Catalog
{
    public const POPULAR_MIN_UNITS = 5;
    public const NEW_DAYS = 30;
    public const LOW_STOCK = 5;
    private const LIVESTOCK = ['aviculture', 'poussin', 'poulet', 'cailles', 'caille', 'pintades', 'pintade', 'oeuf', 'œuf', 'viande'];

    public static function categoryLabel(?string $category): string
    {
        $key = 'catalog.cat.'.Str::lower((string) $category);

        return trans()->has($key) ? __($key) : (string) $category;
    }

    /**
     * LIKE patterns matching a user term inside JSON-translated columns (stored with \uXXXX escapes).
     * @return list<string>
     */
    public static function likePatterns(string $term): array
    {
        $term = trim(str_replace(['%', '_', '\\'], '', $term));
        if ($term === '') {
            return [];
        }
        $escaped = trim(json_encode($term), '"');

        return array_values(array_unique(["%{$term}%", "%{$escaped}%"]));
    }

    /** Apply a translated-column search to a query builder. */
    public static function whereTranslatedLike($query, array $columns, string $term)
    {
        $patterns = self::likePatterns($term);
        if (! $patterns) {
            return $query;
        }

        return $query->where(function ($w) use ($columns, $patterns) {
            foreach ($columns as $col) {
                foreach ($patterns as $pattern) {
                    $w->orWhere($col, 'like', $pattern);
                }
            }
        });
    }

    public static function imageUrl(?string $path): ?string
    {
        return SiteData::imageUrl($path);
    }

    public static function isLivestock(?string $category): bool
    {
        return in_array(Str::lower((string) $category), self::LIVESTOCK, true);
    }

    /** Units sold per product (non-cancelled orders). @return array<int,int> */
    public static function soldUnits(): array
    {
        return DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')->whereNotNull('product_id')
            ->groupBy('product_id')->selectRaw('product_id, sum(qty) as units')
            ->pluck('units', 'product_id')->map(fn ($u) => (int) $u)->all();
    }

    /** Badges derived from data only. @return list<array{key:string,tone:string}> */
    public static function badges(Product $p, array $sold): array
    {
        $out = [];
        if ($p->stock < 1) {
            return [['key' => 'out', 'tone' => 'bg-stone-800 text-white']];
        }
        if ($p->stock <= self::LOW_STOCK) {
            $out[] = ['key' => 'low', 'tone' => 'bg-amber-100 text-amber-900'];
        }
        if ($p->created_at && $p->created_at->gt(now()->subDays(self::NEW_DAYS))) {
            $out[] = ['key' => 'new', 'tone' => 'bg-emerald-100 text-emerald-900'];
        }
        if (($sold[$p->id] ?? 0) >= self::POPULAR_MIN_UNITS) {
            $out[] = ['key' => 'popular', 'tone' => 'bg-brand-100 text-brand-800'];
        }

        return $out;
    }

    public static function whatsappUrl(string $message): ?string
    {
        $number = preg_replace('/\D+/', '', (string) site('whatsapp'));

        return $number ? 'https://wa.me/'.$number.'?text='.rawurlencode($message) : null;
    }

    public static function paymentMethods()
    {
        return PaymentGateway::where('enabled', true)->orderBy('position')->pluck('name');
    }
}
