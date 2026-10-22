<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use App\Livewire\Quiz;
use App\Livewire\Shop;
use App\Livewire\Simulator;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Order;
use App\Models\Product;
use App\Models\SimChoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ApgTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_render(): void
    {
        foreach (['/accueil', '/boutique', '/panier', '/formations', '/simulateur', '/contact', '/connexion', '/inscription'] as $url) {
            $this->get($url)->assertOk();
        }
        $this->get('/')->assertRedirect('/accueil');
    }

    public function test_language_switch_translates_ui(): void
    {
        $this->get('/lang/en')->assertRedirect();
        $this->get('/boutique')->assertSee('Shop');
    }

    public function test_cart_and_transfer_checkout(): void
    {
        $p = Product::first();
        Livewire::test(Shop::class)->call('add', $p->id);
        Livewire::test(Cart::class)
            ->set('name', 'Koffi')->set('phone', '97000000')->set('city', 'Cotonou')->set('gateway', 'westernunion')
            ->call('checkout')->assertHasNoErrors();
        $this->assertSame(1, Order::count());
        $this->assertSame($p->price, (int) Order::first()->total);
    }

    public function test_branching_quiz_follows_chosen_path(): void
    {
        $course = Course::where('slug', 'demarrer-elevage-goliath')->firstOrFail();
        $lesson = $course->lessons()->first();
        $user = User::factory()->create();
        Enrollment::create(['user_id' => $user->id, 'course_id' => $course->id, 'paid' => true]);
        $node = $lesson->nodes()->where('is_start', true)->first();
        $good = $node->options()->where('is_correct', true)->first();

        Livewire::actingAs($user)->test(Quiz::class, ['lessonId' => $lesson->id])
            ->assertSet('nodeId', $node->id)
            ->call('pick', $good->id)->call('next')
            ->assertSet('nodeId', $good->next_node_id)
            ->assertSet('finished', false);
        $this->actingAs(User::factory()->create())->get("/lecon/{$lesson->id}")->assertForbidden(); // not enrolled
        $this->actingAs($user)->get("/lecon/{$lesson->id}")->assertOk();
    }

    public function test_simulator_bad_choice_warns_and_can_go_back(): void
    {
        $bad = SimChoice::where('is_bad', true)->first();
        Livewire::test(Simulator::class)
            ->call('choose', $bad->id)->assertSet('warnChoice', $bad->id)->assertSet('index', 0)
            ->call('confirmBad')->assertSet('index', 1)->assertSet('warnChoice', null)
            ->call('back')->assertSet('index', 0)->assertSet('state.cash', Simulator::START_CASH);
    }

    public function test_admin_panel_access_by_role(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'customer']))->get('/admin')->assertForbidden();
        $admin = User::where('role', 'superadmin')->first();
        $this->actingAs($admin)->get('/admin')->assertOk();
        foreach (['products', 'orders', 'courses', 'quiz-nodes', 'payment-gateways', 'plans', 'sim-steps', 'site-settings'] as $r) {
            $this->actingAs($admin)->get("/admin/$r")->assertOk();
        }
    }
}
