<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index()
    {
        $users = User::orderByDesc('created_at')->paginate(40);
        return view('admin.users.index', compact('users'));
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
        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
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
        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}
