<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Plan extends Model
{
    use Translatable;
    protected $guarded = [];

    public $timestamps = false;
    protected function casts(): array { return ['name' => 'array', 'features' => 'array', 'is_active' => 'boolean']; }
}

