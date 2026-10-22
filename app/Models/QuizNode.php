<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class QuizNode extends Model
{
    use Translatable;
    protected $guarded = [];

    public $timestamps = false;
    protected function casts(): array { return ['question' => 'array', 'explanation' => 'array', 'is_start' => 'boolean']; }

    public function options(): HasMany { return $this->hasMany(QuizOption::class); }
}

