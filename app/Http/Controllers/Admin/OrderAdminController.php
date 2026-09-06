<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderAdminController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->orderByDesc('created_at')->paginate(40);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'items']);
        return view('admin.orders.show', compact('order'));
    }
}
