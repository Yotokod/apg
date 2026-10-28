<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Simulator choice: label, consequence hint, effects (flat key/values), next node, optional bad-decision tip. */
class SimChoice extends Model
{
    use Translatable;

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['label' => 'array', 'hint' => 'array', 'tip' => 'array', 'effects' => 'array', 'goals' => 'array', 'is_bad' => 'boolean'];
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(SimStep::class, 'sim_step_id');
    }
}
