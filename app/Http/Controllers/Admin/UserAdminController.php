<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        return $this->listUsers($request, 'all');
    }

    public function members(Request $request)
    {
        return $this->listUsers($request, 'members');
    }

    public function staff(Request $request)
    {
        return $this->listUsers($request, 'staff');
    }

    protected function listUsers(Request $request, string $scope)
    {
        $q = User::query();

        if ($scope === 'members') {
            $q->where('role', 'buyer');
        } elseif ($scope === 'staff') {
            $q->whereIn('role', ['admin', 'author']);
        }

        if ($request->filled('q')) {
            $term = trim((string) $request->input('q'));
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        if ($request->filled('role') && $scope !== 'members') {
            $role = $request->input('role');
            if (in_array($role, ['admin', 'author', 'buyer'], true)) {
                $q->where('role', $role);
            }
        }

        if ($request->filled('newsletter')) {
            $q->where('newsletter', $request->boolean('newsletter'));
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->input('to'));
        }

        $users = $q->orderByDesc('created_at')->paginate(40)->withQueryString();

        $title = match ($scope) {
            'members' => 'Users (Members)',
            'staff' => 'Admins / Authors',
            default => 'All users',
        };

        return view('admin.users.index', [
            'users' => $users,
            'scope' => $scope,
            'pageTitle' => $title,
        ]);
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|max:60|unique:users,username',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,buyer,author',
            'newsletter' => 'nullable|boolean',
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['newsletter'] = $request->boolean('newsletter');
        User::create($data);

        $redirect = match ($data['role']) {
            'buyer' => route('admin.members.index'),
            default => route('admin.staff.index'),
        };

        return redirect($redirect)->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        $transactions = $user->creditTransactions()->limit(20)->get();

        return view('admin.users.form', compact('user', 'transactions'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'username' => 'nullable|string|max:60|unique:users,username,'.$user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:admin,buyer,author',
            'newsletter' => 'nullable|boolean',
        ]);
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['newsletter'] = $request->boolean('newsletter');
        $user->update($data);

        $redirect = match ($user->role) {
            'buyer' => route('admin.members.index'),
            default => route('admin.staff.index'),
        };

        return redirect($redirect)->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();

        return back()->with('success', 'User deleted.');
    }

    /** Add or remove credits from user wallet. */
    public function addFunds(Request $request, User $user)
    {
        $data = $request->validate([
            'amount' => 'required|numeric',
            'note' => 'nullable|string|max:255',
        ]);

        $amount = (float) $data['amount'];
        if ($amount == 0.0) {
            return back()->with('error', 'Amount cannot be zero.');
        }

        $note = $data['note'] ?: 'Admin adjustment';

        try {
            if ($amount > 0) {
                WalletService::credit($user, $amount, 'admin_adjust', $note, [
                    'admin_id' => Auth::id(),
                ]);
            } else {
                WalletService::debit($user, abs($amount), 'admin_adjust', $note, [
                    'admin_id' => Auth::id(),
                ]);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Wallet updated. New balance: '.$user->fresh()->credit_balance);
    }

    /** Send a plain email to the user (uses configured SMTP). */
    public function sendEmail(Request $request, User $user)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:180',
            'body' => 'required|string|max:10000',
        ]);

        try {
            Mail::raw($data['body'], function ($message) use ($user, $data) {
                $message->to($user->email, $user->name)
                    ->subject($data['subject']);
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Failed to send email: '.$e->getMessage());
        }

        return back()->with('success', 'Email sent to '.$user->email);
    }
}
