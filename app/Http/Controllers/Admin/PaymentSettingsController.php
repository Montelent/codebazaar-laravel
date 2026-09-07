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
                'provider' => 'PAYSTACK',
                'name' => 'Paystack',
                'enabled' => false,
                'is_manual' => false,
                'config' => ['publicKey' => '', 'currency' => 'NGN'],
                'secrets' => ['secretKey' => ''],
                'instructions' => 'Pay securely with card or bank via Paystack.',
            ],
            [
                'provider' => 'MONNIFY',
                'name' => 'Monnify',
                'enabled' => false,
                'is_manual' => false,
                'config' => ['apiKey' => '', 'contractCode' => '', 'currency' => 'NGN', 'mode' => 'sandbox'],
                'secrets' => ['secretKey' => ''],
                'instructions' => 'Pay via Monnify (card, transfer, USSD).',
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
                'provider' => 'CRYPTO_USDT',
                'name' => 'USDT',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['network' => 'TRC20', 'walletAddress' => ''],
                'secrets' => [],
                'instructions' => 'Send exact USDT amount to the wallet address. Include your order ID in the memo if possible.',
            ],
            [
                'provider' => 'CRYPTO_USDC',
                'name' => 'USDC',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['network' => 'ERC20', 'walletAddress' => ''],
                'secrets' => [],
                'instructions' => 'Send exact USDC amount to the wallet address.',
            ],
            [
                'provider' => 'CRYPTO_BNB',
                'name' => 'BNB',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['network' => 'BEP20', 'walletAddress' => ''],
                'secrets' => [],
                'instructions' => 'Send exact BNB amount to the wallet address (BEP20).',
            ],
            [
                'provider' => 'CRYPTO_XRP',
                'name' => 'XRP',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['walletAddress' => '', 'destinationTag' => ''],
                'secrets' => [],
                'instructions' => 'Send XRP to the address. Include the destination tag if provided.',
            ],
            [
                'provider' => 'CRYPTO_GRAM',
                'name' => 'GRAM (TON)',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['network' => 'TON', 'walletAddress' => ''],
                'secrets' => [],
                'instructions' => 'Send GRAM/TON to the wallet address.',
            ],
            [
                'provider' => 'CRYPTO_BTC',
                'name' => 'Bitcoin (BTC)',
                'enabled' => false,
                'is_manual' => true,
                'config' => ['walletAddress' => ''],
                'secrets' => [],
                'instructions' => 'Send exact BTC amount to the wallet address. Network fees are paid by you.',
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

        // Merge any newly added default providers the admin does not have yet
        $byProvider = [];
        foreach ($methods as $m) {
            if (! empty($m['provider'])) {
                $byProvider[$m['provider']] = $m;
            }
        }
        foreach (self::defaults() as $def) {
            if (! isset($byProvider[$def['provider']])) {
                $methods[] = $def;
            }
        }

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

        foreach ($methods as $m) {
            $provider = $m['provider'] ?? '';
            if ($provider === 'STRIPE') {
                SiteSetting::setValue('stripe', [
                    'enabled' => ! empty($m['enabled']),
                    'key' => $m['config']['publishableKey'] ?? '',
                    'secret' => $m['secrets']['secretKey'] ?? '',
                    'webhook' => $m['secrets']['webhookSecret'] ?? '',
                ], 'payments');
            }
            if ($provider === 'PAYSTACK') {
                SiteSetting::setValue('paystack', [
                    'enabled' => ! empty($m['enabled']),
                    'public_key' => $m['config']['publicKey'] ?? '',
                    'secret_key' => $m['secrets']['secretKey'] ?? '',
                    'currency' => $m['config']['currency'] ?? 'NGN',
                ], 'payments');
            }
            if ($provider === 'MONNIFY') {
                SiteSetting::setValue('monnify', [
                    'enabled' => ! empty($m['enabled']),
                    'api_key' => $m['config']['apiKey'] ?? '',
                    'secret_key' => $m['secrets']['secretKey'] ?? '',
                    'contract_code' => $m['config']['contractCode'] ?? '',
                    'mode' => $m['config']['mode'] ?? 'sandbox',
                    'currency' => $m['config']['currency'] ?? 'NGN',
                ], 'payments');
            }
        }

        return back()->with('success', 'Payment methods saved.');
    }
}
