<?php

namespace App\Models;

use App\Models\Concerns\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use Translatable;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'course_title' => 'array',
            'hours' => 'integer',
            'score' => 'integer',
            'issued_at' => 'datetime',
            'revoked_at' => 'datetime',
            'renamed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function isRevoked(): bool
    {
        return $this->revoked_at !== null;
    }

    public function canBeRenamed(): bool
    {
        return $this->renamed_at === null && ! $this->isRevoked();
    }

    public function url(): string
    {
        return route('certificate.show', $this->code);
    }

    /** XXXX-XXXX-XXXX, easier to read aloud or type. */
    public function shortCode(): string
    {
        return implode('-', str_split($this->code, 4));
    }
}
