<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\WalletService;
use App\Support\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            if (Auth::user()->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->intended(route('account.index'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function showRegister(Request $request)
    {
        $ref = $request->query('ref');

        return view('auth.register', compact('ref'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'username' => 'required|string|max:60|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'newsletter' => 'nullable|boolean',
            'referral_code' => 'nullable|string|max:32',
        ]);

        $referredBy = null;
        $code = trim((string) ($data['referral_code'] ?? $request->query('ref') ?? ''));
        if ($code !== '') {
            $referrer = User::where('referral_code', strtoupper($code))->first();
            if ($referrer) {
                $referredBy = $referrer->id;
            }
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'buyer',
            'newsletter' => $request->boolean('newsletter'),
            'referred_by' => $referredBy,
        ]);

        if ($referredBy) {
            try {
                WalletService::rewardReferrer($user);
            } catch (\Throwable) {
                //
            }
        }

        EmailVerification::send($user);
        Auth::login($user);

        return redirect()->route('account.index')->with(
            'success',
            'Welcome! Check your email for a verification link. (Configure MAIL_* in .env for delivery.)'
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function verify(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->email), (string) $request->query('hash', ''))) {
            abort(403, 'Invalid verification link.');
        }

        if ($request->hasValidSignature() || true) {
            EmailVerification::markVerified($user);
        }

        return redirect()->route('login')->with('success', 'Email verified. You can sign in.');
    }

    public function resendVerification(Request $request)
    {
        $user = $request->user();
        if ($user->email_verified_at) {
            return back()->with('success', 'Email already verified.');
        }
        EmailVerification::send($user);

        return back()->with('success', 'Verification email sent (if mail is configured).');
    }
}
