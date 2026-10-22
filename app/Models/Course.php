<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class Course extends Model
{
    use Translatable;
    protected $guarded = [];

    protected function casts(): array { return ['title' => 'array', 'description' => 'array', 'is_active' => 'boolean']; }

    public function modules(): HasMany { return $this->hasMany(CourseModule::class)->orderBy('position'); }

    public function lessons() { return $this->hasManyThrough(Lesson::class, CourseModule::class); }
}

