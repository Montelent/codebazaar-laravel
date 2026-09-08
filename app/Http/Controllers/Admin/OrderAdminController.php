<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = Order::with('user');

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $q->where(function ($w) use ($term) {
                $w->where('email', 'like', "%{$term}%")
                    ->orWhere('payment_provider', 'like', "%{$term}%")
                    ->orWhere('stripe_session_id', 'like', "%{$term}%");
                if (is_numeric($term)) {
                    $w->orWhere('id', (int) $term);
                }
                $w->orWhereHas('user', function ($u) use ($term) {
                    $u->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            });
        }

        if ($request->filled('status')) {
            $q->where('status', $request->input('status'));
        }

        if ($request->filled('provider')) {
            $q->where('payment_provider', $request->input('provider'));
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->input('to'));
        }

        $orders = $q->orderByDesc('created_at')->paginate(40)->withQueryString();

        $providers = Order::query()
            ->whereNotNull('payment_provider')
            ->where('payment_provider', '!=', '')
            ->distinct()
            ->orderBy('payment_provider')
            ->pluck('payment_provider');

        return view('admin.orders.index', compact('orders', 'providers'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items']);

        return view('admin.orders.show', compact('order'));
    }
}
