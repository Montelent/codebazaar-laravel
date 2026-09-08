<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SmtpSettingsController extends Controller
{
    public static function defaults(): array
    {
        return [
            'enabled' => false,
            'mailer' => 'smtp',
            'host' => '',
            'port' => 587,
            'username' => '',
            'password' => '',
            'encryption' => 'tls',
            'from_address' => '',
            'from_name' => 'CodeBazaar',
        ];
    }

    public function edit()
    {
        $smtp = array_merge(self::defaults(), SiteSetting::getValue('smtp', []));
        // Never send the real password to the browser as a visible default
        $smtp['password_set'] = ! empty($smtp['password']);
        $smtp['password'] = '';

        return view('admin.settings.smtp', compact('smtp'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'mailer' => 'required|string|in:smtp,sendmail,log',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|string|in:tls,ssl,none',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:120',
        ]);

        $current = array_merge(self::defaults(), SiteSetting::getValue('smtp', []));

        $password = $current['password'] ?? '';
        if ($request->filled('password')) {
            $password = (string) $request->input('password');
        }

        SiteSetting::setValue('smtp', [
            'enabled' => $request->boolean('enabled'),
            'mailer' => $data['mailer'],
            'host' => $data['host'] ?? '',
            'port' => (int) ($data['port'] ?? 587),
            'username' => $data['username'] ?? '',
            'password' => $password,
            'encryption' => ($data['encryption'] ?? 'tls') === 'none' ? '' : ($data['encryption'] ?? 'tls'),
            'from_address' => $data['from_address'] ?? '',
            'from_name' => $data['from_name'] ?? 'CodeBazaar',
        ], 'mail');

        return back()->with('success', 'SMTP settings saved. Send a test email to verify.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        // Apply latest saved settings before sending
        self::applyConfig();

        try {
            $to = $request->input('test_email');
            Mail::raw(
                "This is a test email from CodeBazaar.\n\nIf you received this, SMTP is working correctly.\nSent at: ".now()->toDateTimeString(),
                function ($message) use ($to) {
                    $message->to($to)->subject('CodeBazaar SMTP test');
                }
            );

            return back()->with('success', 'Test email sent to '.$to.'. Check inbox (and spam).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Test failed: '.$e->getMessage());
        }
    }

    /** Apply SiteSetting SMTP values into Laravel mail config at runtime. */
    public static function applyConfig(): void
    {
        try {
            $smtp = SiteSetting::getValue('smtp', []);
        } catch (\Throwable) {
            return;
        }

        if (! is_array($smtp) || empty($smtp['enabled'])) {
            return;
        }

        $mailer = $smtp['mailer'] ?? 'smtp';
        config([
            'mail.default' => $mailer === 'log' ? 'log' : ($mailer === 'sendmail' ? 'sendmail' : 'smtp'),
            'mail.from.address' => $smtp['from_address'] ?: config('mail.from.address'),
            'mail.from.name' => $smtp['from_name'] ?: config('mail.from.name'),
        ]);

        if (($smtp['mailer'] ?? 'smtp') === 'smtp') {
            $encryption = $smtp['encryption'] ?? 'tls';
            if ($encryption === 'none' || $encryption === '') {
                $encryption = null;
            }
            config([
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $smtp['host'] ?? '',
                'mail.mailers.smtp.port' => (int) ($smtp['port'] ?? 587),
                'mail.mailers.smtp.username' => $smtp['username'] ?? null,
                'mail.mailers.smtp.password' => $smtp['password'] ?? null,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.timeout' => 30,
            ]);
        }
    }
}
