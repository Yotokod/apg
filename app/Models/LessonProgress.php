<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class LessonProgress extends Model
{
    protected $guarded = [];

    public $timestamps = false;
    protected $table = 'lesson_progress';
}

