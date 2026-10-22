<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class QuizOption extends Model
{
    use Translatable;
    protected $guarded = [];

    public $timestamps = false;
    protected function casts(): array { return ['label' => 'array', 'is_correct' => 'boolean']; }
}

