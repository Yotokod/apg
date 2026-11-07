<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use App\Observers\GalleryItemObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(GalleryItemObserver::class)]
class GalleryItem extends Model
{
    use Translatable;

    public const CATEGORIES = [
        'poussins' => 'Poussins', 'poulets' => 'Poulets', 'oeufs' => 'Œufs', 'elevage' => 'Élevage',
        'livraison' => 'Livraison', 'formation' => 'Formation', 'cuisine' => 'Cuisine & recettes',
        'equipe' => 'Équipe', 'autre' => 'Autre',
    ];

    protected $guarded = [];

    /** Known categories + any category already used in data, labelled via gallery.cat.* when translated. @return array<string,string> */
    public static function categoryOptions(?string $locale = null): array
    {
        $keys = array_unique([...array_keys(self::CATEGORIES), ...static::query()->distinct()->pluck('category')->filter()->all()]);

        return collect($keys)->mapWithKeys(fn (string $k) => [$k => self::categoryLabel($k, $locale)])->all();
    }

    public static function categoryLabel(string $key, ?string $locale = null): string
    {
        $tKey = 'gallery.cat.'.$key;

        return trans()->has($tKey, $locale) ? __($tKey, [], $locale) : (self::CATEGORIES[$key] ?? ucfirst(str_replace('-', ' ', $key)));
    }

    protected function casts(): array
    {
        return ['title' => 'array', 'alt' => 'array', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }
}
