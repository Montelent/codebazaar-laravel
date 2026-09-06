<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'products' => Item::count(),
            'orders' => Order::count(),
            'users' => User::count(),
            'revenue' => (float) Order::where('status', 'paid')->sum('total'),
            'posts' => BlogPost::count(),
            'pages' => CmsPage::count(),
            'recentOrders' => Order::orderByDesc('created_at')->limit(8)->get(),
        ]);
    }
}
