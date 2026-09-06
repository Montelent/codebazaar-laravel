<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;

class PaymentSettingsController extends Controller
{
    public static function defaults(): array
    {
        return [
            [
                'provider' => 'STRIPE',
                'name' => 'Stripe',
                'enabled' => false,
                'is_manual' => false,
                'config' => ['publishableKey' => ''],
                'secrets' => ['secretKey' => '', 'webhookSecret' => ''],
                'instructions' => '',
            ],
            [
                'provider' => 'PAYPAL',
                'name' => 'PayPal',
                'enabled' => false,
                'is_manual' => false,
                'config' => ['clientId' => '', 'mode' => 'sandbox'],
                'secrets' => ['clientSecret' => ''],
                'instructions' => '',
            ],
            [
                'provider' => 'BANK_TRANSFER',
                'name' => 'Bank transfer',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['bankName' => '', 'accountName' => '', 'accountNumber' => ''],
                'secrets' => [],
                'instructions' => 'Transfer the order total and send proof to support.',
            ],
            [
                'provider' => 'MANUAL',
                'name' => 'Manual / offline',
                'enabled' => true,
                'is_manual' => true,
                'config' => [],
                'secrets' => [],
                'instructions' => 'Order stays pending until admin marks it paid.',
            ],
        ];
    }

    public function edit()
    {
        $methods = SiteSetting::getValue('payment_methods', self::defaults());

        return view('admin.settings.payments', compact('methods'));
    }

    public function update(Request $request)
    {
        $raw = $request->input('methods_json', '[]');
        $methods = json_decode($raw, true);
        if (! is_array($methods)) {
            return back()->with('error', 'Invalid payment methods payload.');
        }

        SiteSetting::setValue('payment_methods', $methods, 'payments');

        // Mirror Stripe keys into .env-style settings for CheckoutController
        foreach ($methods as $m) {
            if (($m['provider'] ?? '') === 'STRIPE') {
                SiteSetting::setValue('stripe', [
                    'enabled' => ! empty($m['enabled']),
                    'key' => $m['config']['publishableKey'] ?? '',
                    'secret' => $m['secrets']['secretKey'] ?? '',
                    'webhook' => $m['secrets']['webhookSecret'] ?? '',
                ], 'payments');
            }
        }

        return back()->with('success', 'Payment methods saved.');
    }
}
