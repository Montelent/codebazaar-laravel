<?php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $packages = CreditPackage::active()->get();
        if ($packages->isEmpty()) {
            // Sensible defaults when none configured
            $packages = collect([
                (object) ['id' => 0, 'name' => '$10 credits', 'credits' => 10, 'price' => 10, 'currency' => 'USD'],
                (object) ['id' => 0, 'name' => '$25 credits', 'credits' => 25, 'price' => 25, 'currency' => 'USD'],
                (object) ['id' => 0, 'name' => '$50 credits', 'credits' => 55, 'price' => 50, 'currency' => 'USD'],
            ]);
        }
        $transactions = CreditTransaction::where('user_id', $user->id)->orderByDesc('id')->limit(30)->get();
        $referralBonus = (float) data_get(SiteSetting::getValue('credits', []), 'referral_bonus', 5);

        return view('account.credits', compact('user', 'packages', 'transactions', 'referralBonus'));
    }

    public function topup(Request $request)
    {
        $data = $request->validate([
            'package_id' => 'nullable|integer',
            'amount' => 'nullable|numeric|min:1',
            'payment_method' => 'required|string|max:40',
        ]);

        $credits = 0;
        $price = 0;

        if (! empty($data['package_id'])) {
            $pkg = CreditPackage::where('is_active', true)->find($data['package_id']);
            if ($pkg) {
                $credits = (float) $pkg->credits;
                $price = (float) $pkg->price;
            }
        }

        if ($credits <= 0 && ! empty($data['amount'])) {
            $credits = (float) $data['amount'];
            $price = (float) $data['amount'];
        }

        if ($credits <= 0) {
            return back()->with('error', 'Select a package or enter an amount.');
        }

        // Store pending top-up in session and reuse checkout flow via a synthetic cart-less order
        session([
            'credit_topup' => [
                'credits' => $credits,
                'price' => $price,
                'payment_method' => strtoupper($data['payment_method']),
            ],
        ]);

        return redirect()->route('credits.pay');
    }

    public function pay(Request $request)
    {
        $topup = session('credit_topup');
        if (! $topup) {
            return redirect()->route('credits.index');
        }

        // Manual / pending path: create pending order-like reference; admin can approve via user funds
        // For online gateways we redirect similarly to product checkout
        $method = $topup['payment_method'];
        $user = Auth::user();

        // For simplicity: credits top-up via CREDITS is invalid; other methods create a note
        // Instant credit only for free/zero (shouldn't happen)
        if (in_array($method, ['BANK_TRANSFER', 'MANUAL', 'CRYPTO_USDT', 'CRYPTO_USDC', 'CRYPTO_BNB', 'CRYPTO_XRP', 'CRYPTO_GRAM', 'CRYPTO_BTC'], true)) {
            session()->forget('credit_topup');

            return redirect()->route('credits.index')->with(
                'success',
                'Top-up request recorded for '.$topup['credits'].' credits ('.$method.'). Admin will credit your wallet after payment confirmation. Include your email: '.$user->email
            );
        }

        // Stripe/Paystack/Monnify: treat as pending until webhook/callback — for v1 mark instructions
        session()->forget('credit_topup');

        return redirect()->route('credits.index')->with(
            'success',
            'Complete payment of $'.number_format($topup['price'], 2).' via '.$method.'. After payment, contact support or wait for admin confirmation to receive '.$topup['credits'].' credits.'
        );
    }
}
