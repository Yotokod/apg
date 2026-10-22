<?php

namespace App\Livewire;

use App\Models\Order;
use App\Models\PaymentGateway;
use App\Models\Product;
use App\Services\PaymentManager;
use Livewire\Component;

class Cart extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $city = '';
    public string $country = 'Bénin';
    public string $gateway = '';
    public ?int $orderId = null;

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
        $cart = session('cart', []);
        $cart[$id] = max(0, ($cart[$id] ?? 0) + $delta);
        if ($cart[$id] === 0) {
            unset($cart[$id]);
        }
        session(['cart' => $cart]);
        $this->dispatch('cart-updated');
    }

    public function checkout(PaymentManager $payments)
    {
        $this->validate([
            'name' => 'required|min:2', 'phone' => 'required|min:6', 'email' => 'nullable|email',
            'city' => 'required', 'gateway' => 'required|exists:payment_gateways,code',
        ]);
        $lines = $this->lines();
        if ($lines->isEmpty()) {
            return;
        }

        $order = Order::create([
            'user_id' => auth()->id(), 'name' => $this->name, 'phone' => $this->phone, 'email' => $this->email,
            'city' => $this->city, 'country' => $this->country, 'payment_method' => $this->gateway,
            'total' => $lines->sum(fn ($l) => $l['product']->price * $l['qty']),
        ]);
        foreach ($lines as $l) {
            $order->items()->create(['product_id' => $l['product']->id, 'name' => $l['product']->tr('name'), 'price' => $l['product']->price, 'qty' => $l['qty']]);
        }
        session()->forget('cart');
        $this->dispatch('cart-updated');
        $this->orderId = $order->id;

        $url = $payments->initiate($order, PaymentGateway::where('code', $this->gateway)->first());
        if ($url) {
            return redirect()->away($url);
        }
    }

    private function lines()
    {
        $cart = session('cart', []);

        return Product::whereIn('id', array_keys($cart))->get()
            ->map(fn ($p) => ['product' => $p, 'qty' => $cart[$p->id]]);
    }

    public function render()
    {
        $lines = $this->lines();

        return view('livewire.cart', [
            'lines' => $lines,
            'total' => $lines->sum(fn ($l) => $l['product']->price * $l['qty']),
            'gateways' => PaymentGateway::where('enabled', true)->orderBy('position')->get(),
            'order' => $this->orderId ? Order::find($this->orderId) : null,
            'chosen' => $this->orderId ? PaymentGateway::where('code', $this->gateway)->first() : null,
        ])->layout('components.layouts.app', ['title' => __('nav.cart').' | '.site('name')]);
    }
}
