<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SmtpSettingsController extends Controller
{
    /**
     * Built-in provider presets (all ride on SMTP transport except log/sendmail).
     *
     * @return array<string, array{label:string,host:?string,port:?int,encryption:?string,username_hint:string,password_hint:string,help:string}>
     */
    public static function providers(): array
    {
        return [
            'smtp' => [
                'label' => 'Custom SMTP',
                'host' => null,
                'port' => 587,
                'encryption' => 'tls',
                'username_hint' => 'SMTP username',
                'password_hint' => 'SMTP password',
                'help' => 'Use any SMTP server (Hostinger, cPanel, Amazon SES, etc.).',
            ],
            'gmail' => [
                'label' => 'Gmail',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username_hint' => 'Full Gmail address',
                'password_hint' => 'App Password (16 characters)',
                'help' => 'Enable 2-Step Verification, then create an App Password at myaccount.google.com/apppasswords. Do not use your normal Gmail password.',
            ],
            'outlook' => [
                'label' => 'Outlook / Microsoft 365',
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
                'username_hint' => 'Full Outlook / Microsoft email',
                'password_hint' => 'Account password or app password',
                'help' => 'Works for outlook.com, hotmail.com, live.com, and Microsoft 365 work accounts. Host: smtp.office365.com · Port 587 · TLS.',
            ],
            'resend' => [
                'label' => 'Resend',
                'host' => 'smtp.resend.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username_hint' => 'resend (fixed)',
                'password_hint' => 'Resend API key (re_…)',
                'help' => 'Create an API key at resend.com/api-keys. Username is always “resend”. Password = your API key. Verify your domain in Resend for best deliverability.',
            ],
            'hostinger' => [
                'label' => 'Hostinger',
                'host' => 'smtp.hostinger.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username_hint' => 'Full mailbox email',
                'password_hint' => 'Mailbox password',
                'help' => 'Create a mailbox under Hostinger → Emails. Port 465 + SSL, or 587 + TLS.',
            ],
            'sendmail' => [
                'label' => 'Sendmail',
                'host' => null,
                'port' => null,
                'encryption' => null,
                'username_hint' => '',
                'password_hint' => '',
                'help' => 'Uses the server’s local sendmail binary. No host/password needed.',
            ],
            'log' => [
                'label' => 'Log only (debug)',
                'host' => null,
                'port' => null,
                'encryption' => null,
                'username_hint' => '',
                'password_hint' => '',
                'help' => 'Writes emails to the Laravel log instead of sending. Useful for local testing.',
            ],
        ];
    }

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
        $smtp['password_set'] = ! empty($smtp['password']);
        $smtp['password'] = '';
        $providers = self::providers();

        return view('admin.settings.smtp', compact('smtp', 'providers'));
    }

    public function update(Request $request)
    {
        $providerKeys = implode(',', array_keys(self::providers()));

        $data = $request->validate([
            'enabled' => 'nullable|boolean',
            'mailer' => 'required|string|in:'.$providerKeys,
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|string|in:tls,ssl,none',
            'from_address' => 'nullable|email|max:255',
            'from_name' => 'nullable|string|max:120',
        ]);

        $current = array_merge(self::defaults(), SiteSetting::getValue('smtp', []));
        $mailer = $data['mailer'];
        $presets = self::providers();
        $preset = $presets[$mailer] ?? $presets['smtp'];

        $password = $current['password'] ?? '';
        if ($request->filled('password')) {
            $password = (string) $request->input('password');
        }

        // Apply preset host/port/encryption when using a known provider
        $host = $data['host'] ?? '';
        $port = (int) ($data['port'] ?? 587);
        $encryption = $data['encryption'] ?? 'tls';
        $username = $data['username'] ?? '';

        if (in_array($mailer, ['gmail', 'outlook', 'resend', 'hostinger'], true)) {
            $host = $preset['host'] ?? $host;
            $port = (int) ($preset['port'] ?? $port);
            $encryption = $preset['encryption'] ?? $encryption;
        }

        if ($mailer === 'resend' && $username === '') {
            $username = 'resend';
        }

        if ($encryption === 'none') {
            $encryption = '';
        }

        SiteSetting::setValue('smtp', [
            'enabled' => $request->boolean('enabled'),
            'mailer' => $mailer,
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption,
            'from_address' => $data['from_address'] ?? '',
            'from_name' => $data['from_name'] ?? 'CodeBazaar',
        ], 'mail');

        return back()->with('success', 'Mail settings saved. Send a test email to verify.');
    }

    public function test(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        self::applyConfig();

        try {
            $to = $request->input('test_email');
            Mail::raw(
                "This is a test email from CodeBazaar.\n\nIf you received this, mail is working correctly.\nSent at: ".now()->toDateTimeString(),
                function ($message) use ($to) {
                    $message->to($to)->subject('CodeBazaar mail test');
                }
            );

            return back()->with('success', 'Test email sent to '.$to.'. Check inbox (and spam).');
        } catch (\Throwable $e) {
            return back()->with('error', 'Test failed: '.$e->getMessage());
        }
    }

    /** Apply SiteSetting mail values into Laravel mail config at runtime. */
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
        $presets = self::providers();
        $preset = $presets[$mailer] ?? null;

        // Resolve transport
        if ($mailer === 'log') {
            config(['mail.default' => 'log']);
        } elseif ($mailer === 'sendmail') {
            config(['mail.default' => 'sendmail']);
        } else {
            // gmail, outlook, resend, hostinger, smtp → SMTP transport
            config(['mail.default' => 'smtp']);

            $host = $smtp['host'] ?? '';
            $port = (int) ($smtp['port'] ?? 587);
            $encryption = $smtp['encryption'] ?? 'tls';
            $username = $smtp['username'] ?? null;
            $password = $smtp['password'] ?? null;

            if ($preset && in_array($mailer, ['gmail', 'outlook', 'resend', 'hostinger'], true)) {
                $host = $preset['host'] ?: $host;
                $port = (int) ($preset['port'] ?: $port);
                $encryption = $preset['encryption'] ?? $encryption;
            }

            if ($mailer === 'resend' && (empty($username) || $username === '')) {
                $username = 'resend';
            }

            if ($encryption === 'none' || $encryption === '') {
                $encryption = null;
            }

            config([
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => $host,
                'mail.mailers.smtp.port' => $port,
                'mail.mailers.smtp.username' => $username,
                'mail.mailers.smtp.password' => $password,
                'mail.mailers.smtp.encryption' => $encryption,
                'mail.mailers.smtp.timeout' => 30,
            ]);
        }

        config([
            'mail.from.address' => $smtp['from_address'] ?: config('mail.from.address'),
            'mail.from.name' => $smtp['from_name'] ?: config('mail.from.name'),
        ]);
    }
}
