<?php

namespace App\Support;

use App\Models\PaymentGateway;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/** Read-only data shared by layout widgets (nav, footer, home). */
class SiteData
{
    /** Active product categories with counts. @return array<string,int> */
    public static function categories(): array
    {
        return Product::where('is_active', true)->selectRaw('category, count(*) as n')
            ->groupBy('category')->orderBy('category')->pluck('n', 'category')->all();
    }

    /** @return list<string> */
    public static function paymentMethods(): array
    {
        return PaymentGateway::where('enabled', true)->orderBy('position')->pluck('name')->all();
    }

    public static function imageUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $path = ltrim($path, '/');
        $thumb = 'thumbs/'.$path.'.webp';

        return asset('storage/'.(Storage::disk('public')->exists($thumb) ? $thumb : $path));
    }

    public static function whatsappUrl(?string $text = null): ?string
    {
        $number = preg_replace('/\D+/', '', (string) site('whatsapp'));
        if (! $number) {
            return null;
        }

        return 'https://wa.me/'.$number.($text ? '?text='.rawurlencode($text) : '');
    }

    /** Route URL when the route exists, otherwise null. */
    public static function url(string $name, array $params = []): ?string
    {
        return Route::has($name) ? route($name, $params) : null;
    }

    public static function hasTable(string $table): bool
    {
        return Schema::hasTable($table);
    }
}
