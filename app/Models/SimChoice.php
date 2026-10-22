<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class SimChoice extends Model
{
    use Translatable;
    protected $guarded = [];

    public $timestamps = false;
    protected function casts(): array { return ['label' => 'array', 'tip' => 'array', 'is_bad' => 'boolean']; }
}

