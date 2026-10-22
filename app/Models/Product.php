<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Product extends Model
{
    use Translatable;
    protected $guarded = [];

    protected function casts(): array { return ['name' => 'array', 'description' => 'array', 'is_active' => 'boolean']; }
}

