<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;

/** Simulator parameter (value + unit + status apg|generique|hypothese), editable in the admin. */
class SimParam extends Model
{
    use Translatable;

    public const STATUSES = ['apg', 'generique', 'hypothese'];

    public const GROUPS = ['flock', 'feed', 'building', 'heat', 'health', 'costs', 'growth', 'laying', 'sales', 'scenario'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['label' => 'array', 'note' => 'array', 'value' => 'float'];
    }
}
