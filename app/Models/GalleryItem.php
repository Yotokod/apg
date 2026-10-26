<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class GalleryItem extends Model
{
    use Translatable;

    public const CATEGORIES = [
        'poussins' => 'Poussins', 'poulets' => 'Poulets', 'oeufs' => 'Œufs', 'elevage' => 'Élevage',
        'livraison' => 'Livraison', 'formation' => 'Formation', 'cuisine' => 'Cuisine & recettes',
        'equipe' => 'Équipe', 'autre' => 'Autre',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['title' => 'array', 'alt' => 'array', 'is_featured' => 'boolean', 'is_active' => 'boolean'];
    }
}
