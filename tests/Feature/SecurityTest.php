<?php

namespace Tests\Feature;

use App\Livewire\Cart;
use App\Livewire\Quiz;
use App\Models\Lesson;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Models\QuizNode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_plain_admin_cannot_manage_users_or_gateways_but_superadmin_can(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get('/admin/users')->assertForbidden();
        $this->actingAs($admin)->get('/admin/payment-gateways')->assertForbidden();
        $this->actingAs($admin)->get('/admin/products')->assertOk();
        $this->flushSession();
        $this->actingAs(User::where('role', 'superadmin')->first())->get('/admin/users')->assertOk();
    }

    public function test_quiz_requires_enrollment(): void
    {
        $node = QuizNode::where('is_start', true)->firstOrFail();
        Livewire::actingAs(User::factory()->create())->test(Quiz::class, ['lessonId' => $node->lesson_id])->assertForbidden();
        $this->assertNotNull(Lesson::find($node->lesson_id));
    }

    public function test_quiz_state_is_locked_against_tampering(): void
    {
        $node = QuizNode::where('is_start', true)->firstOrFail();
        $course = Lesson::findOrFail($node->lesson_id)->module->course_id;
        $u = User::factory()->create();
        \App\Models\Enrollment::create(['user_id' => $u->id, 'course_id' => $course, 'paid' => true]);

        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::actingAs($u)->test(Quiz::class, ['lessonId' => $node->lesson_id])->set('correct', 99);
    }

    public function test_checkout_rejects_disabled_gateway(): void
    {
        PaymentGateway::where('code', 'paypal')->update(['enabled' => false]);
        $p = Product::where('is_active', true)->where('stock', '>', 0)->firstOrFail();
        session(['cart' => [$p->id => 1]]);
        Livewire::test(Cart::class)->set('name', 'Koffi')->set('phone', '97000000')->set('city', 'Cotonou')->set('gateway', 'paypal')
            ->call('checkout')->assertHasErrors('gateway');
        $this->assertSame(0, \App\Models\Order::count());
    }

    public function test_checkout_decrements_stock_and_refuses_oversell(): void
    {
        $p = Product::where('is_active', true)->firstOrFail();
        $p->update(['stock' => 1]);
        session(['cart' => [$p->id => 2]]);
        Livewire::test(Cart::class)->set('name', 'Koffi')->set('phone', '97000000')->set('city', 'Cotonou')->set('gateway', 'westernunion')
            ->call('checkout');
        $this->assertSame(0, \App\Models\Order::count());
        $this->assertSame(1, (int) $p->fresh()->stock);
    }

    public function test_order_confirmation_is_not_readable_by_foreign_order_id(): void
    {
        $o = \App\Models\Order::create(['name' => 'X', 'phone' => '1', 'total' => 999]);
        $this->expectException(\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException::class);
        Livewire::test(Cart::class)->set('orderId', $o->id);
    }

    public function test_security_headers_present(): void
    {
        $this->get('/accueil')->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
