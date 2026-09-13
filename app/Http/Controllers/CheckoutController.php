<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\PaymentSettingsController;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SiteSetting;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        $total = collect($cart)->sum(fn ($row) => $row['price'] * ($row['qty'] ?? 1));
        $methods = $this->enabledMethods();
        $creditBalance = Auth::check() ? (float) Auth::user()->credit_balance : 0;

        return view('checkout.show', compact('cart', 'total', 'methods', 'creditBalance'));
    }

    public function place(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $data = $request->validate([
            'email' => 'required|email',
            'payment_method' => 'required|string|max:40',
        ]);

        $email = $data['email'];
        $method = strtoupper($data['payment_method']);
        $total = (float) collect($cart)->sum(fn ($row) => $row['price'] * ($row['qty'] ?? 1));
        $isFree = $total <= 0;

        if ($isFree) {
            $method = 'FREE';
        }

        if (! $isFree && $method === 'CREDITS') {
            if (! Auth::check()) {
                return back()->with('error', 'Log in to pay with credits.');
            }
            if (! Auth::user()->hasCredits($total)) {
                return back()->with('error', 'Insufficient credits. Top up your wallet or choose another payment method.');
            }
        }

        if (! $isFree && $method !== 'CREDITS') {
            $enabled = collect($this->enabledMethods())->pluck('provider')->map(fn ($p) => strtoupper($p))->all();
            if (! in_array($method, $enabled, true)) {
                return back()->with('error', 'Selected payment method is not available.');
            }
        }

        $order = DB::transaction(function () use ($cart, $email, $total, $isFree, $method) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'email' => $email,
                'status' => ($isFree || $method === 'CREDITS') ? 'paid' : 'pending',
                'total' => $total,
                'currency' => 'USD',
                'payment_provider' => strtolower($method),
                'paid_with_credits' => $method === 'CREDITS',
            ]);

            foreach ($cart as $row) {
                for ($i = 0; $i < ($row['qty'] ?? 1); $i++) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'item_id' => $row['item_id'],
                        'slug' => $row['slug'],
                        'title' => $row['title'],
                        'thumbnail_url' => $row['thumbnail_url'] ?? null,
                        'license_type' => $row['license_type'],
                        'price' => $row['price'],
                    ]);
                }
                Item::where('id', $row['item_id'])->increment('sales_count', $row['qty'] ?? 1);
            }

            if ($method === 'CREDITS' && Auth::check()) {
                WalletService::debit(Auth::user(), $total, 'purchase', 'Order #'.$order->id, [
                    'order_id' => $order->id,
                ]);
            }

            return $order;
        });

        if ($isFree || $method === 'CREDITS') {
            session()->forget('cart');

            return redirect()->route('account.downloads')->with('success', 'Order complete. You can download your files.');
        }

        return match ($method) {
            'STRIPE' => $this->payWithStripe($order, $cart, $email),
            'PAYSTACK' => $this->payWithPaystack($order, $email),
            'MONNIFY' => $this->payWithMonnify($order, $email),
            default => $this->payManual($order, $method),
        };
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        $ref = $request->get('reference') ?: $request->get('paymentReference');

        if ($sessionId) {
            $order = Order::where('stripe_session_id', $sessionId)->first();
            if ($order) {
                $order->update(['status' => 'paid']);
            }
        }

        if ($ref) {
            $order = Order::where('payment_reference', $ref)->orWhere('id', $ref)->first();
            if ($order && $order->status !== 'paid') {
                // Verify Paystack if applicable
                if (strtolower((string) $order->payment_provider) === 'paystack') {
                    $secret = data_get(SiteSetting::getValue('paystack', []), 'secret_key');
                    if ($secret) {
                        $res = Http::withToken($secret)->get('https://api.paystack.co/transaction/verify/'.urlencode($ref));
                        if ($res->successful() && data_get($res->json(), 'data.status') === 'success') {
                            $order->update(['status' => 'paid', 'payment_reference' => $ref]);
                        }
                    }
                } else {
                    $order->update(['status' => 'paid', 'payment_reference' => $ref]);
                }
            }
        }

        session()->forget('cart');

        return redirect()->route('account.purchases')->with('success', 'Payment received. Thank you!');
    }

    protected function enabledMethods(): array
    {
        $methods = SiteSetting::getValue('payment_methods', PaymentSettingsController::defaults());
        $list = array_values(array_filter($methods, fn ($m) => ! empty($m['enabled'])));

        // Always offer credits when logged in (virtual method)
        if (Auth::check()) {
            array_unshift($list, [
                'provider' => 'CREDITS',
                'name' => 'Wallet credits',
                'enabled' => true,
                'is_manual' => false,
                'instructions' => 'Pay instantly from your credit balance.',
            ]);
        }

        return $list;
    }

    protected function methodConfig(string $provider): ?array
    {
        $methods = SiteSetting::getValue('payment_methods', PaymentSettingsController::defaults());
        foreach ($methods as $m) {
            if (strtoupper($m['provider'] ?? '') === strtoupper($provider)) {
                return $m;
            }
        }

        return null;
    }

    protected function payWithStripe(Order $order, array $cart, string $email)
    {
        $cfg = $this->methodConfig('STRIPE');
        $secret = $cfg['secrets']['secretKey'] ?? data_get(SiteSetting::getValue('stripe', []), 'secret') ?: config('services.stripe.secret');

        if (empty($secret)) {
            return back()->with('error', 'Stripe is enabled but secret key is missing. Configure it in Admin → Payments.');
        }

        \Stripe\Stripe::setApiKey($secret);
        $lineItems = [];
        foreach ($cart as $row) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $row['title'].' ('.$row['license_type'].')'],
                    'unit_amount' => (int) round($row['price'] * 100),
                ],
                'quantity' => $row['qty'] ?? 1,
            ];
        }

        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'customer_email' => $email,
            'line_items' => $lineItems,
            'success_url' => route('checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.show'),
            'metadata' => ['order_id' => (string) $order->id],
        ]);

        $order->update(['stripe_session_id' => $session->id]);

        return redirect($session->url);
    }

    protected function payWithPaystack(Order $order, string $email)
    {
        $cfg = $this->methodConfig('PAYSTACK');
        $secret = $cfg['secrets']['secretKey'] ?? data_get(SiteSetting::getValue('paystack', []), 'secret_key');
        $currency = $cfg['config']['currency'] ?? data_get(SiteSetting::getValue('paystack', []), 'currency', 'NGN');

        if (empty($secret)) {
            return back()->with('error', 'Paystack is enabled but secret key is missing.');
        }

        // Amount in kobo/cents
        $amount = (int) round($order->total * 100);
        $ref = 'CBZ-'.$order->id.'-'.Str::upper(Str::random(8));
        $order->update(['payment_reference' => $ref]);

        $res = Http::withToken($secret)->post('https://api.paystack.co/transaction/initialize', [
            'email' => $email,
            'amount' => $amount,
            'currency' => $currency,
            'reference' => $ref,
            'callback_url' => route('checkout.success'),
            'metadata' => ['order_id' => $order->id],
        ]);

        $url = data_get($res->json(), 'data.authorization_url');
        if (! $url) {
            return back()->with('error', 'Paystack init failed: '.(data_get($res->json(), 'message') ?: 'unknown error'));
        }

        return redirect($url);
    }

    protected function payWithMonnify(Order $order, string $email)
    {
        $cfg = $this->methodConfig('MONNIFY');
        $apiKey = $cfg['config']['apiKey'] ?? data_get(SiteSetting::getValue('monnify', []), 'api_key');
        $secret = $cfg['secrets']['secretKey'] ?? data_get(SiteSetting::getValue('monnify', []), 'secret_key');
        $contract = $cfg['config']['contractCode'] ?? data_get(SiteSetting::getValue('monnify', []), 'contract_code');
        $mode = $cfg['config']['mode'] ?? 'sandbox';
        $base = $mode === 'live' ? 'https://api.monnify.com' : 'https://sandbox.monnify.com';

        if (empty($apiKey) || empty($secret) || empty($contract)) {
            return back()->with('error', 'Monnify is enabled but API credentials or contract code are missing.');
        }

        $tokenRes = Http::withBasicAuth($apiKey, $secret)->post($base.'/api/v1/auth/login');
        $accessToken = data_get($tokenRes->json(), 'responseBody.accessToken');
        if (! $accessToken) {
            return back()->with('error', 'Monnify authentication failed.');
        }

        $ref = 'CBZ-'.$order->id.'-'.Str::upper(Str::random(8));
        $order->update(['payment_reference' => $ref]);

        $init = Http::withToken($accessToken)->post($base.'/api/v1/merchant/transactions/init-transaction', [
            'amount' => (float) $order->total,
            'customerName' => Auth::user()->name ?? $email,
            'customerEmail' => $email,
            'paymentReference' => $ref,
            'paymentDescription' => 'Order #'.$order->id,
            'currencyCode' => $cfg['config']['currency'] ?? 'NGN',
            'contractCode' => $contract,
            'redirectUrl' => route('checkout.success'),
            'paymentMethods' => ['CARD', 'ACCOUNT_TRANSFER'],
        ]);

        $url = data_get($init->json(), 'responseBody.checkoutUrl');
        if (! $url) {
            return back()->with('error', 'Monnify init failed: '.(data_get($init->json(), 'responseMessage') ?: 'unknown'));
        }

        return redirect($url);
    }

    protected function payManual(Order $order, string $method)
    {
        $cfg = $this->methodConfig($method);
        session()->forget('cart');

        return redirect()
            ->route('checkout.pending', $order)
            ->with('success', 'Order placed. Complete payment using the instructions below. Admin will mark it paid after confirmation.');
    }

    public function pending(Order $order)
    {
        if (Auth::id() && (int) $order->user_id !== (int) Auth::id()) {
            abort(403);
        }
        $cfg = $this->methodConfig(strtoupper((string) $order->payment_provider));

        return view('checkout.pending', compact('order', 'cfg'));
    }
}
