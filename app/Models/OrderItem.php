<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class OrderItem extends Model
{
    protected $guarded = [];

    public $timestamps = false;
}

