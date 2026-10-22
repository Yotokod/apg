<?php

namespace App\Models\Concerns;

/** JSON-translatable attributes: $translatable = ['name'] -> $model->tr('name') */
trait Translatable
{
    public function tr(string $field): ?string
    {
        $v = $this->{$field};
        if (! is_array($v)) {
            return $v;
        }
        return $v[app()->getLocale()] ?? $v['fr'] ?? (reset($v) ?: null);
    }
}
