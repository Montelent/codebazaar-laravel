<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = User::where('newsletter', true)->orderBy('email')->get(['id', 'name', 'email', 'email_verified_at']);
        $allUsers = User::count();
        $logs = [];
        if (DB::getSchemaBuilder()->hasTable('newsletter_logs')) {
            $logs = DB::table('newsletter_logs')->orderByDesc('id')->limit(20)->get();
        }

        return view('admin.newsletter.index', compact('subscribers', 'allUsers', 'logs'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'audience' => 'required|in:newsletter,all,verified',
        ]);

        $query = User::query()->whereNotNull('email');
        if ($data['audience'] === 'newsletter') {
            $query->where('newsletter', true);
        } elseif ($data['audience'] === 'verified') {
            $query->whereNotNull('email_verified_at');
        }

        $users = $query->get();
        $sent = 0;
        $errors = 0;

        foreach ($users as $user) {
            try {
                Mail::raw(strip_tags($data['body'])."\n\n— CodeBazaar", function ($message) use ($user, $data) {
                    $message->to($user->email, $user->name ?: $user->email)
                        ->subject($data['subject']);
                });
                // Prefer HTML when possible
                try {
                    Mail::html($data['body'], function ($message) use ($user, $data) {
                        $message->to($user->email, $user->name ?: $user->email)
                            ->subject($data['subject']);
                    });
                } catch (\Throwable) {
                    // raw already attempted
                }
                $sent++;
            } catch (\Throwable $e) {
                $errors++;
            }
        }

        if (DB::getSchemaBuilder()->hasTable('newsletter_logs')) {
            DB::table('newsletter_logs')->insert([
                'subject' => $data['subject'],
                'body' => $data['body'],
                'recipients' => $sent,
                'sent_by' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $msg = "Newsletter queued/sent to {$sent} recipient(s).";
        if ($errors) {
            $msg .= " {$errors} failed (check MAIL_* in .env)."
;
        }

        return back()->with('success', $msg);
    }
}
