<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Str;

class Seo
{
    public static function siteName(): string
    {
        $general = SiteSetting::getValue('general', []);

        return (string) ($general['site_name'] ?? config('app.name', 'CodeBazaar'));
    }

    public static function defaultDescription(): string
    {
        $general = SiteSetting::getValue('general', []);

        return (string) ($general['site_description']
            ?? 'Digital marketplace for code, scripts, themes and plugins.');
    }

    public static function defaultImage(): string
    {
        $schema = SiteSetting::getValue('schema', []);
        $logo = trim((string) ($schema['organization_logo'] ?? ''));

        return $logo !== '' ? $logo : url('/favicon.ico');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    public static function make(array $overrides = []): array
    {
        $site = self::siteName();
        $rawTitle = trim((string) ($overrides['title'] ?? ''));
        if ($rawTitle === '' || $rawTitle === 'null') {
            $title = $site;
        } elseif (! str_contains($rawTitle, $site)) {
            $title = $rawTitle.' · '.$site;
        } else {
            $title = $rawTitle;
        }

        $description = trim((string) ($overrides['description'] ?? self::defaultDescription()));
        if ($description === '' || $description === 'null') {
            $description = self::defaultDescription();
        }
        $description = Str::limit(strip_tags($description), 160, '…');

        $canonical = $overrides['canonical'] ?? url()->current();
        $canonical = self::normalizeUrl((string) $canonical);

        $robots = $overrides['robots'] ?? null;
        if (! empty($overrides['noindex'])) {
            $robots = 'noindex, nofollow';
        }
        $robots = $robots ?: 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';

        $ogTitle = trim((string) ($overrides['og_title'] ?? $rawTitle ?: $site));
        if ($ogTitle === '') {
            $ogTitle = $site;
        }
        $ogDescription = Str::limit(strip_tags((string) ($overrides['og_description'] ?? $description)), 200, '…');
        $ogImage = trim((string) ($overrides['og_image'] ?? self::defaultImage()));
        if ($ogImage !== '' && ! str_starts_with($ogImage, 'http')) {
            $ogImage = url($ogImage);
        }

        return [
            'title' => $title,
            'description' => $description,
            'keywords' => trim((string) ($overrides['keywords'] ?? '')),
            'canonical' => $canonical,
            'robots' => $robots,
            'og_title' => $ogTitle,
            'og_description' => $ogDescription,
            'og_image' => $ogImage,
            'og_type' => $overrides['og_type'] ?? 'website',
            'og_url' => $canonical,
            'og_site_name' => $site,
            'twitter_card' => $overrides['twitter_card'] ?? 'summary_large_image',
            'twitter_title' => $ogTitle,
            'twitter_description' => $ogDescription,
            'twitter_image' => $ogImage,
            'theme_color' => '#82b440',
        ];
    }

    public static function normalizeUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return url('/');
        }
        if (! str_starts_with($url, 'http')) {
            $url = url($url);
        }
        $parts = parse_url($url);
        if (! is_array($parts) || empty($parts['host'])) {
            return $url;
        }
        $path = $parts['path'] ?? '/';
        $scheme = $parts['scheme'] ?? 'https';
        $host = $parts['host'];
        $port = isset($parts['port']) ? ':'.$parts['port'] : '';

        return $scheme.'://'.$host.$port.$path;
    }

    public static function rules(): array
    {
        return [
            'seo_title' => 'nullable|string|max:200',
            'seo_description' => 'nullable|string|max:320',
            'seo_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|string|max:500',
            'og_title' => 'nullable|string|max:200',
            'og_description' => 'nullable|string|max:320',
            'og_image' => 'nullable|string|max:1000',
            'robots' => 'nullable|string|max:80',
            'focus_keyword' => 'nullable|string|max:120',
        ];
    }

    /** @return array<string, string|null> */
    public static function extract(array $data): array
    {
        $keys = [
            'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
            'og_title', 'og_description', 'og_image', 'robots', 'focus_keyword',
        ];
        $out = [];
        foreach ($keys as $k) {
            if (array_key_exists($k, $data)) {
                $out[$k] = $data[$k] !== '' ? $data[$k] : null;
            }
        }

        return $out;
    }
}
