<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EmailVerification
{
    public static function send(User $user): void
    {
        $token = Str::random(64);

        if (DB::getSchemaBuilder()->hasTable('email_verification_tokens')) {
            DB::table('email_verification_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => hash('sha256', $token), 'created_at' => now()]
            );
        }

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addDay(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Also append token query for hosts that strip signatures oddly
        $url .= (str_contains($url, '?') ? '&' : '?').'token='.$token;

        try {
            Mail::raw(
                "Welcome to CodeBazaar!\n\nPlease verify your email by opening this link:\n{$url}\n\nThis link expires in 24 hours.",
                function ($message) use ($user) {
                    $message->to($user->email, $user->name ?: $user->email)
                        ->subject('Verify your CodeBazaar email');
                }
            );
        } catch (\Throwable $e) {
            // Mail may be unconfigured on shared hosting — still store token
            report($e);
        }
    }

    public static function markVerified(User $user): void
    {
        $user->forceFill(['email_verified_at' => now()])->save();
        if (DB::getSchemaBuilder()->hasTable('email_verification_tokens')) {
            DB::table('email_verification_tokens')->where('email', $user->email)->delete();
        }
    }
}
