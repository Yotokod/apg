<?php

namespace App\Support;

use App\Models\SimParam;
use App\Models\SimStep;

/** Builds the client-side simulator config (params + story graph + UI strings) for the current locale. */
class SimulatorConfig
{
    public const VERSION = '2.0';

    public static function build(): array
    {
        $params = SimParam::orderBy('position')->orderBy('id')->get();
        $steps = SimStep::with('choices')->whereNotNull('scene')->orderBy('position')->orderBy('id')->get();

        return [
            'version' => self::VERSION,
            'revision' => self::revision($params, $steps),
            'locale' => app()->getLocale(),
            'currency' => site('currency', 'FCFA'),
            'date' => now()->toDateString(),
            'params' => $params->mapWithKeys(fn (SimParam $p) => [$p->key => (float) $p->value]),
            'meta' => $params->map(fn (SimParam $p) => [
                'key' => $p->key, 'unit' => $p->unit, 'status' => $p->status, 'group' => $p->group,
                'label' => $p->tr('label'), 'note' => $p->tr('note'),
            ])->values(),
            'story' => [
                'start' => $steps->first()?->code,
                'nodes' => $steps->mapWithKeys(fn (SimStep $s) => [$s->code => self::node($s)]),
            ],
            'i18n' => __('sim'),
        ];
    }

    private static function node(SimStep $s): array
    {
        return [
            'code' => $s->code, 'chapter' => (int) $s->chapter, 'scene' => $s->scene, 'kind' => $s->kind, 'icon' => $s->icon,
            'next' => $s->next, 'title' => $s->tr('title'), 'body' => $s->tr('body'),
            'choices' => $s->choices->map(fn ($c) => [
                'code' => $c->code ?: 'c'.$c->id, 'icon' => $c->icon, 'label' => $c->tr('label'), 'hint' => $c->tr('hint'),
                'next' => $c->next, 'effects' => (object) ($c->effects ?? []), 'goals' => $c->goals ?: null,
                'bad' => (bool) $c->is_bad, 'tip' => $c->tr('tip'),
            ])->values(),
        ];
    }

    /** Changes when admins edit the story or params, so stale saved runs are discarded client-side. */
    private static function revision($params, $steps): string
    {
        return substr(md5(json_encode([$params->pluck('value', 'key'), $steps->map(fn ($s) => [$s->code, $s->next, $s->choices->pluck('next', 'code')])])), 0, 10);
    }
}
