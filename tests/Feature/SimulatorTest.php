<?php

namespace Tests\Feature;

use App\Models\SimParam;
use App\Models\SimStep;
use App\Models\User;
use App\Support\SimulatorConfig;
use Database\Seeders\SimulatorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_page_renders_with_config(): void
    {
        $this->get('/simulateur')->assertOk()
            ->assertSee('x-data="simulator(', false)
            ->assertSee(__('sim.ui.title'));
    }

    public function test_config_json_is_complete_and_valid(): void
    {
        $config = json_decode(json_encode(SimulatorConfig::build()), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('intro', $config['story']['start']);
        $this->assertSame(900.0, (float) $config['params']['chick_price_benin']);
        $this->assertSame(3000.0, (float) $config['params']['frozen_price_kg']);
        $this->assertSame(500.0, (float) $config['params']['min_flock']);
        $this->assertArrayHasKey('report', $config['story']['nodes']);
        $this->assertSame('ending', $config['story']['nodes']['report']['kind']);
        $this->assertIsArray($config['i18n']['errors']);

        foreach ($config['story']['nodes'] as $node) {
            foreach ($node['choices'] as $choice) {
                $next = $choice['next'] ?: $node['next'];
                foreach (explode(';', (string) $next) as $part) {
                    $target = str_contains($part, '=') ? explode('=', $part)[1] : $part;
                    $this->assertArrayHasKey(trim($target), $config['story']['nodes'], "{$node['code']} → $target");
                }
                if ($choice['bad']) {
                    $this->assertNotEmpty($choice['tip'], "bad choice {$choice['code']} needs a tip");
                }
            }
        }
    }

    public function test_params_are_seeded_with_statuses_and_seeding_is_idempotent(): void
    {
        $this->assertGreaterThan(50, SimParam::count());
        $this->assertSame([], SimParam::whereNotIn('status', SimParam::STATUSES)->pluck('key')->all());
        $this->assertSame('apg', SimParam::where('key', 'chick_price_benin')->value('status'));
        $this->assertSame('hypothese', SimParam::where('key', 'feed_price_kg')->value('status'));

        SimParam::where('key', 'feed_price_kg')->update(['value' => 420]);
        $counts = [SimParam::count(), SimStep::count()];
        $this->seed(SimulatorSeeder::class);
        $this->assertSame($counts, [SimParam::count(), SimStep::count()]);
        $this->assertSame(420.0, SimParam::where('key', 'feed_price_kg')->value('value'), 'admin edits are kept');
    }

    public function test_admin_param_resource_requires_admin(): void
    {
        $this->get('/admin/sim-params')->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin/sim-params')->assertForbidden();
        $admin = User::where('role', 'superadmin')->first();
        $this->actingAs($admin)->get('/admin/sim-params')->assertOk()->assertSee('chick_price_benin');
        $this->actingAs($admin)->get('/admin/sim-steps')->assertOk();
    }
}
