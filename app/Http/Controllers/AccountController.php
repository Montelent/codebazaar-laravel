<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function index()
    {
        return view('account.index');
    }

    public function settings()
    {
        return view('account.settings', ['user' => Auth::user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:60', 'alpha_dash', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,'.$user->id],
            'bio' => ['nullable', 'string', 'max:1000'],
            'newsletter' => ['nullable', 'boolean'],
        ]);

        $emailChanged = strcasecmp((string) $user->email, (string) $data['email']) !== 0;

        $user->name = $data['name'];
        $user->username = $data['username'] ?? $user->username;
        $user->email = $data['email'];
        $user->bio = $data['bio'] ?? null;
        $user->newsletter = $request->boolean('newsletter');

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('success', $emailChanged
            ? 'Profile updated. Please verify your new email address.'
            : 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $user->password = $data['password'];
        $user->save();

        return back()->with('success', 'Password changed successfully.');
    }

    public function purchases(Request $request)
    {
        $userId = Auth::id();
        $email = Auth::user()?->email ?? $request->get('email');

        $orders = Order::with('items')
            ->where('status', 'paid')
            ->where(function ($q) use ($userId, $email) {
                if ($userId) {
                    $q->where('user_id', $userId);
                }
                if ($email) {
                    $q->orWhere('email', $email);
                }
            })
            ->latest()
            ->get()
            ->unique('id')
            ->values();

        return view('account.purchases', compact('orders', 'email'));
    }

    public function downloads(Request $request)
    {
        $userId = Auth::id();
        $email = Auth::user()?->email ?? $request->get('email');

        $items = OrderItem::query()
            ->whereHas('order', function ($q) use ($userId, $email) {
                $q->where('status', 'paid')
                    ->where(function ($w) use ($userId, $email) {
                        if ($userId) {
                            $w->where('user_id', $userId);
                        }
                        if ($email) {
                            $w->orWhere('email', $email);
                        }
                    });
            })
            ->with(['item', 'order'])
            ->latest()
            ->get()
            ->unique(fn ($row) => $row->item_id.'|'.($row->license_type ?? 'regular'))
            ->values();

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
