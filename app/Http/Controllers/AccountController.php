<?php

namespace App\Http\Controllers;

use App\Models\Item;
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

    public function downloadFile(int $itemId, Request $request)
    {
        $item = Item::findOrFail($itemId);
        $owned = OrderItem::where('item_id', $itemId)
            ->whereHas('order', fn ($q) => $q->where('status', 'paid')->where(function ($w) {
                $w->where('user_id', Auth::id())->orWhere('email', Auth::user()?->email);
            }))
            ->exists();

        if (! $owned && ! Auth::user()?->isAdmin()) {
            abort(403, 'Purchase required.');
        }

        $files = $item->downloadFilesList();
        if (count($files) === 0) {
            return back()->with('error', 'No download file is set for this product.');
        }

        $index = (int) $request->get('file', 0);
        if ($index < 0 || $index >= count($files)) {
            $index = 0;
        }

        return redirect()->away($files[$index]['url']);
    }
}
