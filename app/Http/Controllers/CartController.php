<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $total = collect($cart)->sum(fn ($row) => $row['price'] * ($row['qty'] ?? 1));
        return view('cart.index', compact('cart', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|exists:items,id',
            'license_type' => 'in:regular,extended',
            'qty' => 'integer|min:1|max:99',
        ]);
        $item = Item::findOrFail($data['item_id']);
        $license = $data['license_type'] ?? 'regular';
        $qty = $data['qty'] ?? 1;
        $price = $license === 'extended' ? $item->effectiveExtendedPrice() : $item->effectiveRegularPrice();
        $cart = session('cart', []);
        $key = $item->id . '_' . $license;
        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'item_id' => $item->id,
                'slug' => $item->slug,
                'title' => $item->title,
                'thumbnail_url' => $item->thumbnail_url,
                'license_type' => $license,
                'price' => $price,
                'qty' => $qty,
            ];
        }
        session(['cart' => $cart]);
        return back()->with('success', 'Added to cart.');
    }

    public function remove(string $key)
    {
        $cart = session('cart', []);
        unset($cart[$key]);
        session(['cart' => $cart]);
        return back()->with('success', 'Removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');
        return redirect()->route('cart.index');
    }
}
