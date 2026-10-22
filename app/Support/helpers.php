<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (! function_exists('site')) {
    /** Read a brand/identity value: DB override first, then config/site_identity.php. */
    function site(string $key, mixed $default = null): mixed
    {
        $overrides = Cache::rememberForever('settings.all', function () {
            try {
                return Schema::hasTable('settings') ? Setting::pluck('value', 'key')->all() : [];
            } catch (Throwable) {
                return [];
            }
        });

        $value = $overrides[$key] ?? null;
        if ($value !== null && $value !== '') {
            return $value;
        }

        $value = config("site_identity.$key", $default);
        if (is_array($value) && isset($value['fr'])) {
            return $value[app()->getLocale()] ?? $value['fr'];
        }

        return $value;
    }
}

if (! function_exists('fcfa')) {
    function fcfa(int|float $n): string
    {
        return number_format($n, 0, ',', ' ').' '.site('currency', 'FCFA');
    }
}
