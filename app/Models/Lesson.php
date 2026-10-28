<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Lesson extends Model
{
    use Translatable;
    protected $guarded = [];

    public $timestamps = false;
    protected function casts(): array { return ['title' => 'array', 'content' => 'array', 'scenes' => 'array']; }

    public function module(): BelongsTo { return $this->belongsTo(CourseModule::class, 'course_module_id'); }

    public function nodes(): HasMany { return $this->hasMany(QuizNode::class); }
}

