<?php

namespace Database\Seeders;

use App\Models\SimParam;
use App\Models\SimStep;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Idempotent: parameters and story nodes are created when missing, never overwritten (admin edits are kept).
 * Legacy linear steps (no scene) from the first simulator are replaced by the story graph.
 */
class SimulatorSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/simulator.json'), true, flags: JSON_THROW_ON_ERROR);

        DB::transaction(function () use ($data) {
            $this->seedParams($data['params']);
            SimStep::whereNull('scene')->get()->each->delete();
            $this->seedNodes($data['nodes']);
        });
    }

    private function seedParams(array $params): void
    {
        foreach ($params as $i => $p) {
            SimParam::firstOrCreate(['key' => $p['key']], [
                'value' => $p['value'], 'unit' => $p['unit'], 'status' => $p['status'], 'group' => $p['group'],
                'label' => $p['label'], 'note' => $p['note'] ?? null, 'position' => $i,
            ]);
        }
    }

    private function seedNodes(array $nodes): void
    {
        foreach ($nodes as $i => $n) {
            $step = SimStep::firstOrCreate(['code' => $n['code']], [
                'position' => $i, 'chapter' => $n['chapter'], 'scene' => $n['scene'], 'kind' => $n['kind'], 'icon' => $n['icon'] ?? null,
                'next' => $n['next'], 'title' => $n['title'], 'body' => $n['body'],
            ]);
            if (! $step->wasRecentlyCreated) {
                continue;
            }
            foreach ($n['choices'] as $j => $c) {
                $step->choices()->create([
                    'code' => $c['code'], 'position' => $j, 'icon' => $c['icon'] ?? null, 'label' => $c['label'], 'hint' => $c['hint'] ?? null,
                    'next' => $c['next'] ?? null, 'effects' => $c['effects'] ?: null, 'goals' => $c['goals'] ?? null,
                    'is_bad' => $c['bad'] ?? false, 'tip' => $c['tip'] ?? null,
                ]);
            }
        }
    }
}
