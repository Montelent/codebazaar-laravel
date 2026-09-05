<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        $total = collect($cart)->sum(fn ($row) => $row['price'] * ($row['qty'] ?? 1));
        return view('checkout.show', compact('cart', 'total'));
    }

    public function place(Request $request)
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index');
        }
        $email = $request->validate(['email' => 'required|email'])['email'];
        $total = collect($cart)->sum(fn ($row) => $row['price'] * ($row['qty'] ?? 1));
        $isFree = $total <= 0;

        if (! $isFree && empty(config('services.stripe.secret'))) {
            return back()->with('error', 'Paid checkout requires STRIPE_SECRET. Free items can still be claimed.');
        }

        $order = DB::transaction(function () use ($cart, $email, $total, $isFree) {
            $order = Order::create([
                'user_id' => Auth::id(),
                'email' => $email,
                'status' => $isFree ? 'paid' : 'pending',
                'total' => $total,
                'currency' => 'USD',
                'payment_provider' => $isFree ? 'free' : 'stripe',
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
            return $order;
        });

        if ($isFree) {
            session()->forget('cart');
            return redirect()->route('account.downloads')->with('success', 'Order complete. You can download your files.');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $lineItems = [];
        foreach ($cart as $row) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => $row['title'] . ' (' . $row['license_type'] . ')'],
                    'unit_amount' => (int) round($row['price'] * 100),
                ],
                'quantity' => $row['qty'] ?? 1,
            ];
        }
        $session = \Stripe\Checkout\Session::create([
            'mode' => 'payment',
            'customer_email' => $email,
            'line_items' => $lineItems,
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.show'),
            'metadata' => ['order_id' => (string) $order->id],
        ]);
        $order->update(['stripe_session_id' => $session->id]);
        return redirect($session->url);
    }

    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');
        if ($sessionId) {
            $order = Order::where('stripe_session_id', $sessionId)->first();
            if ($order) {
                $order->update(['status' => 'paid']);
            }
        }
        session()->forget('cart');
        return redirect()->route('account.purchases')->with('success', 'Payment received. Thank you!');
    }
}
