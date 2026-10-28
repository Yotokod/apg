<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Simulator story node (scene + narration); its choices are the graph edges. */
class SimStep extends Model
{
    use Translatable;

    public const KINDS = ['goal', 'story', 'flock', 'building', 'heat', 'behaviour', 'feed', 'laying', 'incubation', 'sale', 'ending'];

    public const SCENES = ['farm', 'chicks', 'house', 'site', 'thermo', 'huddle', 'panting', 'feed', 'vaccine', 'alert', 'growth', 'eggs', 'incubator', 'truck', 'report'];

    protected $guarded = [];

    public $timestamps = false;

    protected function casts(): array
    {
        return ['title' => 'array', 'body' => 'array'];
    }

    public function choices(): HasMany
    {
        return $this->hasMany(SimChoice::class)->orderBy('position')->orderBy('id');
    }
}
