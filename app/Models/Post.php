<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use Translatable;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['title' => 'array', 'excerpt' => 'array', 'body' => 'array', 'published_at' => 'datetime', 'is_active' => 'boolean'];
    }
}
