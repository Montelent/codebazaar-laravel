<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'products' => Item::count(),
            'orders' => Order::where('status', 'paid')->count(),
            'revenue' => Order::where('status', 'paid')->sum('total'),
            'users' => User::count(),
            'posts' => BlogPost::count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }
}
