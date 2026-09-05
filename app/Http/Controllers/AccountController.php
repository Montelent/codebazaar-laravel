<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.index');
    }

    public function purchases(Request $request)
    {
        $email = Auth::user()?->email ?? $request->get('email');
        $orders = Order::with('items')
            ->when($email, fn ($q) => $q->where('email', $email)->orWhere('user_id', Auth::id()))
            ->where('status', 'paid')
            ->latest()
            ->get();
        return view('account.purchases', compact('orders', 'email'));
    }

    public function downloads(Request $request)
    {
        $email = Auth::user()?->email ?? $request->get('email');
        $items = OrderItem::query()
            ->whereHas('order', function ($q) use ($email) {
                $q->where('status', 'paid')
                    ->when($email, fn ($qq) => $qq->where(function ($w) use ($email) {
                        $w->where('email', $email)->orWhere('user_id', Auth::id());
                    }));
            })
            ->with('item')
            ->latest()
            ->get();
        return view('account.downloads', compact('items'));
    }

    public function downloadFile(int $itemId)
    {
        $item = \App\Models\Item::findOrFail($itemId);
        $owned = OrderItem::where('item_id', $itemId)
            ->whereHas('order', fn ($q) => $q->where('status', 'paid')->where(function ($w) {
                $w->where('user_id', Auth::id())->orWhere('email', Auth::user()?->email);
            }))
            ->exists();
        if (! $owned && ! Auth::user()?->isAdmin()) {
            abort(403, 'Purchase required.');
        }
        if (empty($item->main_file_url)) {
            return back()->with('error', 'No download file is set for this product.');
        }
        return redirect()->away($item->main_file_url);
    }
}
