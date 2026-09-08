<?php

namespace App\Services;

use App\Http\Controllers\Admin\SmtpSettingsController;
use App\Models\BlogPost;
use App\Models\Item;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterService
{
    /** Available shortcodes shown in admin UI. */
    public static function shortcodeHelp(): array
    {
        return [
            '{{weekly_products}}' => 'Products created or updated in the last 7 days (styled cards).',
            '{{weekly_posts}}' => 'Blog posts published or updated in the last 7 days (styled cards).',
            '{{new_products}}' => 'Alias of weekly_products.',
            '{{new_posts}}' => 'Alias of weekly_posts.',
            '{{site_name}}' => 'Site / store name.',
            '{{site_url}}' => 'Homepage URL.',
            '{{user_name}}' => 'Recipient display name (per email).',
            '{{user_email}}' => 'Recipient email (per email).',
            '{{unsubscribe_note}}' => 'Short note about managing preferences in the account.',
        ];
    }

    /**
     * @param  array{audience:string,user_ids?:array<int>,limit?:int|null}  $options
     * @return Collection<int, User>
     */
    public static function resolveRecipients(array $options): Collection
    {
        $audience = $options['audience'] ?? 'newsletter';
        $limit = isset($options['limit']) && $options['limit'] ? (int) $options['limit'] : null;

        $query = User::query()->whereNotNull('email')->where('email', '!=', '');

        switch ($audience) {
            case 'newsletter':
                $query->where('newsletter', true);
                break;
            case 'verified':
                $query->whereNotNull('email_verified_at');
                break;
            case 'all':
                break;
            case 'selected':
                $ids = array_values(array_filter(array_map('intval', $options['user_ids'] ?? [])));
                if (count($ids) === 0) {
                    return collect();
                }
                $query->whereIn('id', $ids);
                break;
            case 'first_100_newsletter':
                $query->where('newsletter', true)->orderByDesc('updated_at')->limit(100);

                return $query->get();
            case 'first_100_all':
                $query->orderByDesc('updated_at')->limit(100);

                return $query->get();
            default:
                $query->where('newsletter', true);
        }

        if ($limit) {
            $query->orderByDesc('updated_at')->limit($limit);
        } else {
            $query->orderBy('email');
        }

        return $query->get();
    }

    public static function expandShortcodes(string $html, ?User $user = null): string
    {
        $siteName = (string) (SiteSetting::get('site_name', config('app.name', 'CodeBazaar')) ?: 'CodeBazaar');
        $siteUrl = rtrim((string) config('app.url'), '/') ?: url('/');

        $replacements = [
            '{{weekly_products}}' => self::renderWeeklyProductsHtml(),
            '{{new_products}}' => self::renderWeeklyProductsHtml(),
            '{{weekly_posts}}' => self::renderWeeklyPostsHtml(),
            '{{new_posts}}' => self::renderWeeklyPostsHtml(),
            '{{site_name}}' => e($siteName),
            '{{site_url}}' => e($siteUrl),
            '{{user_name}}' => e($user?->name ?: 'there'),
            '{{user_email}}' => e($user?->email ?: ''),
            '{{unsubscribe_note}}' => 'You received this because you have an account or newsletter preference on '.e($siteName).'. Manage preferences from your account page.',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $html);
    }

    public static function renderWeeklyProductsHtml(): string
    {
        $since = now()->subDays(7);
        $items = Item::query()
            ->where(function ($q) use ($since) {
                $q->where('created_at', '>=', $since)
                    ->orWhere('updated_at', '>=', $since);
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', 'approved');
            })
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        if ($items->isEmpty()) {
            return '<p style="color:#64748b;font-size:14px;margin:0;">No new or updated products this week.</p>';
        }

        $html = '';
        foreach ($items as $item) {
            $url = route('item.show', [$item->slug, $item->id]);
            $title = e($item->title);
            $thumb = $item->thumbnail_url
                ? '<img src="'.e($item->thumbnail_url).'" alt="" width="72" height="72" style="display:block;width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid #e2e8f0;">'
                : '<div style="width:72px;height:72px;border-radius:8px;background:#f1f5f9;"></div>';
            $price = ($item->is_free || (float) ($item->regular_price ?? 0) <= 0)
                ? 'Free'
                : '$'.number_format((float) $item->regular_price, 2);
            $cat = e($item->category?->name ?? '');

            $html .= <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 14px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
  <tr>
    <td style="padding:12px;width:72px;vertical-align:top;">{$thumb}</td>
    <td style="padding:12px 14px 12px 0;vertical-align:top;">
      <a href="{$url}" style="color:#0f172a;font-size:15px;font-weight:700;text-decoration:none;line-height:1.3;">{$title}</a>
      <div style="margin-top:4px;font-size:12px;color:#64748b;">{$cat}</div>
      <div style="margin-top:6px;font-size:14px;font-weight:600;color:#82b440;">{$price}</div>
    </td>
  </tr>
</table>
HTML;
        }

        return $html;
    }

    public static function renderWeeklyPostsHtml(): string
    {
        $since = now()->subDays(7);
        $posts = BlogPost::query()
            ->where('status', 'published')
            ->where(function ($q) use ($since) {
                $q->where('published_at', '>=', $since)
                    ->orWhere('updated_at', '>=', $since)
                    ->orWhere('created_at', '>=', $since);
            })
            ->orderByDesc('published_at')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        if ($posts->isEmpty()) {
            return '<p style="color:#64748b;font-size:14px;margin:0;">No new or updated posts this week.</p>';
        }

        $html = '';
        foreach ($posts as $post) {
            $url = route('blog.show', $post->slug);
            $title = e($post->title);
            $excerpt = e(Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 140, '…'));
            $cat = e($post->category ?: 'Blog');

            $html .= <<<HTML
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 14px;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
  <tr>
    <td style="padding:14px 16px;">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#82b440;font-weight:700;">{$cat}</div>
      <a href="{$url}" style="display:block;margin-top:4px;color:#0f172a;font-size:15px;font-weight:700;text-decoration:none;">{$title}</a>
      <p style="margin:8px 0 0;font-size:13px;line-height:1.5;color:#64748b;">{$excerpt}</p>
    </td>
  </tr>
</table>
HTML;
        }

        return $html;
    }

    public static function defaultWeeklyBody(): string
    {
        return <<<'HTML'
<p>Hi {{user_name}},</p>
<p>Here’s what was new or updated on <strong>{{site_name}}</strong> this week.</p>

<h2 style="font-size:16px;margin:24px 0 12px;color:#0f172a;">Products this week</h2>
{{weekly_products}}

<h2 style="font-size:16px;margin:24px 0 12px;color:#0f172a;">From the blog</h2>
{{weekly_posts}}

<p style="margin-top:24px;"><a href="{{site_url}}">Visit the store →</a></p>
<p style="font-size:12px;color:#94a3b8;">{{unsubscribe_note}}</p>
HTML;
    }

    /**
     * @return array{sent:int,errors:int,skipped:int}
     */
    public static function send(string $subject, string $bodyHtml, Collection $recipients, ?int $sentBy = null): array
    {
        try {
            SmtpSettingsController::applyConfig();
        } catch (\Throwable) {
            // continue with .env mail
        }

        $siteName = (string) (SiteSetting::get('site_name', config('app.name', 'CodeBazaar')) ?: 'CodeBazaar');
        $siteUrl = rtrim((string) config('app.url'), '/') ?: url('/');
        $primary = (string) (SiteSetting::get('primary_color', '#82b440') ?: '#82b440');

        $sent = 0;
        $errors = 0;

        foreach ($recipients as $user) {
            try {
                $expanded = self::expandShortcodes($bodyHtml, $user);
                $html = view('emails.newsletter', [
                    'subject' => $subject,
                    'bodyHtml' => $expanded,
                    'siteName' => $siteName,
                    'siteUrl' => $siteUrl,
                    'primary' => $primary,
                    'user' => $user,
                ])->render();

                Mail::html($html, function ($message) use ($user, $subject) {
                    $message->to($user->email, $user->name ?: $user->email)
                        ->subject($subject);
                });
                $sent++;
            } catch (\Throwable) {
                $errors++;
            }
        }

        if (DB::getSchemaBuilder()->hasTable('newsletter_logs')) {
            try {
                DB::table('newsletter_logs')->insert([
                    'subject' => $subject,
                    'body' => $bodyHtml,
                    'recipients' => $sent,
                    'sent_by' => $sentBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable) {
                // ignore log failures
            }
        }

        return ['sent' => $sent, 'errors' => $errors, 'skipped' => 0];
    }

    /** Notify about a single blog post. */
    public static function notifyPost(BlogPost $post, string $audience, ?int $sentBy = null): array
    {
        $siteName = (string) (SiteSetting::get('site_name', config('app.name', 'CodeBazaar')) ?: 'CodeBazaar');
        $url = route('blog.show', $post->slug);
        $title = e($post->title);
        $excerpt = e(Str::limit(strip_tags((string) ($post->excerpt ?: $post->content)), 180, '…'));

        $body = <<<HTML
<p>Hi {{user_name}},</p>
<p>There’s a new post on <strong>{{site_name}}</strong>:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #e2e8f0;border-radius:10px;background:#ffffff;">
  <tr><td style="padding:16px;">
    <a href="{$url}" style="color:#0f172a;font-size:18px;font-weight:700;text-decoration:none;">{$title}</a>
    <p style="margin:10px 0 0;font-size:14px;line-height:1.5;color:#64748b;">{$excerpt}</p>
    <p style="margin:16px 0 0;"><a href="{$url}" style="display:inline-block;background:#82b440;color:#fff;text-decoration:none;padding:10px 16px;border-radius:8px;font-weight:600;font-size:13px;">Read the post</a></p>
  </td></tr>
</table>
<p style="font-size:12px;color:#94a3b8;">{{unsubscribe_note}}</p>
HTML;

        $recipients = self::resolveRecipients(['audience' => $audience]);

        return self::send('New on '.$siteName.': '.$post->title, $body, $recipients, $sentBy);
    }
}
