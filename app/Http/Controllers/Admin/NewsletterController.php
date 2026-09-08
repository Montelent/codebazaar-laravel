<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NewsletterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NewsletterController extends Controller
{
    public function index()
    {
        $subscribers = User::where('newsletter', true)->orderBy('email')->get(['id', 'name', 'email', 'email_verified_at']);
        $allUsers = User::orderBy('email')->get(['id', 'name', 'email', 'role', 'newsletter', 'email_verified_at']);
        $logs = [];
        if (DB::getSchemaBuilder()->hasTable('newsletter_logs')) {
            $logs = DB::table('newsletter_logs')->orderByDesc('id')->limit(20)->get();
        }

        $shortcodes = NewsletterService::shortcodeHelp();
        $defaultBody = NewsletterService::defaultWeeklyBody();

        return view('admin.newsletter.index', compact('subscribers', 'allUsers', 'logs', 'shortcodes', 'defaultBody'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string',
            'audience' => 'required|in:newsletter,all,verified,selected,first_100_newsletter,first_100_all',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'template' => 'nullable|in:custom,weekly',
        ]);

        $body = $data['body'];
        if (($data['template'] ?? '') === 'weekly' && ! str_contains($body, '{{weekly_')) {
            $body = NewsletterService::defaultWeeklyBody();
        }

        $recipients = NewsletterService::resolveRecipients([
            'audience' => $data['audience'],
            'user_ids' => $data['user_ids'] ?? [],
        ]);

        if ($recipients->isEmpty()) {
            return back()->withInput()->with('error', 'No recipients matched that audience.');
        }

        $result = NewsletterService::send(
            $data['subject'],
            $body,
            $recipients,
            $request->user()->id
        );

        $msg = "Newsletter sent to {$result['sent']} recipient(s).";
        if ($result['errors']) {
            $msg .= " {$result['errors']} failed — check SMTP settings.";
        }

        return back()->with($result['errors'] && ! $result['sent'] ? 'error' : 'success', $msg);
    }
}
