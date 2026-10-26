<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Services\PaymentManager;
use App\Support\CartStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

class Cart extends Component
{
    public const STEP_CART = 1;
    public const STEP_INFO = 2;
    public const STEP_PAYMENT = 3;

    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $city = '';
    public string $country = 'Bénin';
    public string $gateway = '';
    #[Locked]
    public ?int $orderId = null;
    public int $step = self::STEP_CART;

    public function mount(): void
    {
        if ($u = auth()->user()) {
            $this->name = $u->name;
            $this->email = $u->email;
            $this->phone = (string) $u->phone;
        }
    }

    public function qty(int $id, int $delta): void
    {
        $current = CartStore::raw()[$id] ?? null;
        if ($current === null) {
            return;
        }
        $this->setQty($id, $current + $delta);
    }

    public function setQty(int $id, int $qty): void
    {
        if ($qty < 1) {
            CartStore::remove($id);
        } else {
            CartStore::set($id, $qty);
        }
        $this->dispatch('cart-updated');
    }

    public function remove(int $id): void
    {
        CartStore::remove($id);
        $this->dispatch('cart-updated');
    }

    public function goTo(int $step): void
    {
        if (CartStore::lines()->isEmpty()) {
            $this->step = self::STEP_CART;

            return;
        }
        if ($step >= self::STEP_PAYMENT) {
            $this->validate($this->infoRules());
        }
        $this->step = max(self::STEP_CART, min(self::STEP_PAYMENT, $step));
    }

    private function infoRules(): array
    {
        return ['name' => 'required|min:2|max:120', 'phone' => 'required|min:6|max:30', 'email' => 'nullable|email|max:160', 'city' => 'required|max:120'];
    }

    public function checkout(PaymentManager $payments)
    {
        $this->validate($this->infoRules() + ['gateway' => ['required', Rule::exists('payment_gateways', 'code')->where('enabled', true)]]);
        $lines = CartStore::lines();
        if ($lines->isEmpty()) {
            return;
        }

        $order = DB::transaction(function () use ($lines) {
            foreach ($lines as $l) {
                $fresh = \App\Models\Product::lockForUpdate()->find($l['product']->id);
                if (! $fresh || $fresh->stock < $l['qty']) {
                    return null;
                }
                $fresh->decrement('stock', $l['qty']);
            }
            $order = Order::create([
                'user_id' => auth()->id(), 'name' => $this->name, 'phone' => $this->phone, 'email' => $this->email,
                'city' => $this->city, 'country' => $this->country, 'payment_method' => $this->gateway,
                'total' => CartStore::total($lines),
            ]);
            foreach ($lines as $l) {
                $order->items()->create(['product_id' => $l['product']->id, 'name' => $l['product']->tr('name'), 'price' => $l['product']->price, 'qty' => $l['qty']]);
            }

            return $order;
        });
        if (! $order) {
            $this->addError('gateway', __('catalog.shop.unavailable'));

            return;
        }
        session()->push('my_orders', $order->id);
        session()->forget('cart');
        $this->dispatch('cart-updated');
        $this->orderId = $order->id;

        $url = $payments->initiate($order, PaymentGateway::where('code', $this->gateway)->first());
        if ($url) {
            return redirect()->away($url);
        }
    }

    public function render()
    {
        $lines = CartStore::lines();

        return view('livewire.cart', [
            'lines' => $lines,
            'total' => CartStore::total($lines),
            'count' => $lines->sum('qty'),
            'gateways' => PaymentGateway::where('enabled', true)->orderBy('position')->get(),
            'order' => $this->orderId && in_array($this->orderId, session('my_orders', [])) ? Order::find($this->orderId) : null,
            'chosen' => $this->orderId ? PaymentGateway::where('code', $this->gateway)->first() : null,
        ])->layout('components.layouts.app', ['title' => __('nav.cart').' | '.site('name')]);
    }
}
