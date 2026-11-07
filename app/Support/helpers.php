<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('site')) {
    /** Keys whose stored value is an uploaded file path on the public disk (returned as a URL). */
    defined('SITE_FILE_KEYS') || define('SITE_FILE_KEYS', ['logo', 'favicon', 'org.signature', 'org.stamp', 'seo.og_image']);

    /**
     * Brand/identity value. Order: DB "key.<locale>" -> DB "key" -> config/site_identity.php (dot notation).
     * Settings are memoised per request; flush with site_flush() after saving.
     */
    function site(string $key, mixed $default = null): mixed
    {
        $overrides = site_overrides();
        $locale = app()->getLocale();

        $value = null;
        foreach (["$key.$locale", $key] as $k) {
            if (($overrides[$k] ?? '') !== '') {
                $value = $overrides[$k];
                break;
            }
        }
        if ($value === null) {
            $value = config("site_identity.$key", $default);
            if (is_array($value) && isset($value['fr'])) {
                return $value[$locale] ?? $value['fr'];
            }

            return $value;
        }

        if (in_array($key, SITE_FILE_KEYS, true) && ! str_starts_with($value, 'http') && ! str_starts_with($value, '/')) {
            return asset('storage/'.ltrim($value, '/'));
        }

        return $value;
    }

    /** @return array<string,string|null> */
    function site_overrides(bool $refresh = false): array
    {
        static $memo = null;
        if ($refresh || $memo === null) {
            $memo = Cache::rememberForever('settings.all', function () {
                try {
                    return Schema::hasTable('settings') ? Setting::pluck('value', 'key')->all() : [];
                } catch (Throwable) {
                    return [];
                }
            });
        }

        return $memo;
    }

    function site_flush(): void
    {
        Cache::forget('settings.all');
        site_overrides(true);
    }
}

if (! function_exists('fcfa')) {
    function fcfa(int|float $n): string
    {
        return number_format($n, 0, ',', ' ').' '.site('currency', 'FCFA');
    }
}

if (! function_exists('category_icon')) {
    function category_icon(?string $c): string
    {
        $c = mb_strtolower((string) $c);

        return match (true) {
            str_contains($c, 'aviculture'), str_contains($c, 'poussin') => 'fa-dove',
            str_contains($c, 'viande'), str_contains($c, 'poulet') => 'fa-drumstick-bite',
            str_contains($c, 'oeuf'), str_contains($c, 'œuf') => 'fa-egg',
            str_contains($c, 'caille') => 'fa-feather',
            str_contains($c, 'pintade') => 'fa-feather-pointed',
            str_contains($c, 'formation') => 'fa-graduation-cap',
            str_contains($c, 'guide') => 'fa-book',
            default => 'fa-leaf',
        };
    }
}
