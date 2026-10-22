<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gateway registry + hosted-checkout initiation.
 * initiate() returns a redirect URL, or null when the order awaits manual confirmation.
 */
class PaymentManager
{
    /** Config fields exposed in Admin > Payment gateways (code => [name, type, fields]). */
    public const GATEWAYS = [
        'fedapay' => ['FedaPay', 'api', ['secret_key', 'public_key', 'sandbox']],
        'kkiapay' => ['KKiaPay', 'api', ['public_key', 'private_key', 'secret', 'sandbox']],
        'cinetpay' => ['CinetPay', 'api', ['site_id', 'api_key', 'secret_key']],
        'paypal' => ['PayPal', 'api', ['client_id', 'client_secret', 'sandbox']],
        'paydunya' => ['Paydunya', 'api', ['master_key', 'private_key', 'public_key', 'token', 'sandbox']],
        'westernunion' => ['Western Union', 'transfer', []],
        'moneygram' => ['MoneyGram', 'transfer', []],
        'ria' => ['Ria Money Transfer', 'transfer', []],
    ];

    public function initiate(Order $order, ?PaymentGateway $gw): ?string
    {
        if (! $gw || $gw->type === 'transfer') {
            return null;
        }
        $c = $gw->config ?? [];

        try {
            return match ($gw->code) {
                'fedapay' => $this->fedapay($order, $c),
                'cinetpay' => $this->cinetpay($order, $c),
                'paypal' => $this->paypal($order, $c),
                default => null, // widget-based (KKiaPay) / manual confirmation by admin
            };
        } catch (\Throwable $e) {
            Log::warning('Payment init failed', ['gateway' => $gw->code, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private function fedapay(Order $o, array $c): ?string
    {
        $base = ! empty($c['sandbox']) ? 'https://sandbox-api.fedapay.com' : 'https://api.fedapay.com';
        $r = Http::withToken($c['secret_key'] ?? '')->post("$base/v1/transactions", [
            'description' => "Order #{$o->id}", 'amount' => $o->total, 'currency' => ['iso' => 'XOF'],
            'callback_url' => route('payment.return', $o),
            'customer' => ['firstname' => $o->name, 'email' => $o->email, 'phone_number' => ['number' => $o->phone, 'country' => 'bj']],
        ])->throw()->json();
        $id = data_get($r, 'v1/transaction.id') ?? data_get($r, 'transaction.id');
        $o->update(['payment_ref' => (string) $id]);
        $tok = Http::withToken($c['secret_key'] ?? '')->post("$base/v1/transactions/$id/token")->throw()->json();

        return $tok['url'] ?? null;
    }

    private function cinetpay(Order $o, array $c): ?string
    {
        $r = Http::post('https://api-checkout.cinetpay.com/v2/payment', [
            'apikey' => $c['api_key'] ?? '', 'site_id' => $c['site_id'] ?? '', 'transaction_id' => "APG{$o->id}".time(),
            'amount' => (int) (ceil($o->total / 5) * 5), 'currency' => 'XOF', 'description' => "Order #{$o->id}",
            'return_url' => route('payment.return', $o), 'notify_url' => route('payment.webhook', 'cinetpay'),
            'channels' => 'ALL', 'customer_name' => $o->name, 'customer_surname' => $o->name,
        ])->throw()->json();

        return data_get($r, 'data.payment_url');
    }

    private function paypal(Order $o, array $c): ?string
    {
        $base = ! empty($c['sandbox']) ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
        $token = Http::asForm()->withBasicAuth($c['client_id'] ?? '', $c['client_secret'] ?? '')
            ->post("$base/v1/oauth2/token", ['grant_type' => 'client_credentials'])->throw()->json('access_token');
        $usd = number_format($o->total / 600, 2, '.', ''); // approx XOF->USD; adjust in settings later
        $r = Http::withToken($token)->post("$base/v2/checkout/orders", [
            'intent' => 'CAPTURE',
            'purchase_units' => [['reference_id' => (string) $o->id, 'amount' => ['currency_code' => 'USD', 'value' => $usd]]],
            'application_context' => ['return_url' => route('payment.return', $o), 'cancel_url' => route('cart')],
        ])->throw()->json();
        $o->update(['payment_ref' => $r['id'] ?? null]);
        foreach ($r['links'] ?? [] as $l) {
            if (($l['rel'] ?? '') === 'approve') {
                return $l['href'];
            }
        }

        return null;
    }
}
