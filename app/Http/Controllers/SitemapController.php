<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Category;
use App\Models\CmsPage;
use App\Models\Item;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Schema;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /account',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /install',
            'Disallow: /setup',
            'Disallow: /email',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function xml(): Response
    {
        $urls = $this->collectUrls();

        $body = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $body .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $row) {
            $body .= "  <url>\n";
            $body .= '    <loc>'.htmlspecialchars($row['loc'], ENT_XML1)."</loc>\n";
            $body .= '    <lastmod>'.htmlspecialchars($row['lastmod'], ENT_XML1)."</lastmod>\n";
            $body .= '    <changefreq>'.htmlspecialchars($row['changefreq'], ENT_XML1)."</changefreq>\n";
            $body .= '    <priority>'.htmlspecialchars($row['priority'], ENT_XML1)."</priority>\n";
            $body .= "  </url>\n";
        }

        $body .= '</urlset>'."\n";

        return response($body, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function html()
    {
        $groups = [
            'Home' => [['title' => 'Homepage', 'url' => url('/')]],
            'Categories' => [],
            'Products' => [],
            'Blog' => [['title' => 'Blog index', 'url' => url('/blog')]],
            'Pages' => [],
        ];

        try {
            if (Schema::hasTable('categories')) {
                foreach (Category::orderBy('name')->get() as $c) {
                    if ($this->isNoindex($c->robots ?? null)) {
                        continue;
                    }
                    $groups['Categories'][] = [
                        'title' => $c->name,
                        'url' => route('category', $c->slug),
                    ];
                }
            }
            if (Schema::hasTable('items')) {
                foreach (Item::approved()->orderBy('title')->get(['id', 'title', 'slug', 'robots']) as $item) {
                    if ($this->isNoindex($item->robots ?? null)) {
                        continue;
                    }
                    $groups['Products'][] = [
                        'title' => $item->title,
                        'url' => route('item.show', [$item->slug, $item->id]),
                    ];
                }
            }
            if (Schema::hasTable('blog_posts')) {
                foreach (BlogPost::where('status', 'published')->orderByDesc('published_at')->get() as $post) {
                    if ($this->isNoindex($post->robots ?? null)) {
                        continue;
                    }
                    $groups['Blog'][] = [
                        'title' => $post->title,
                        'url' => route('blog.show', $post->slug),
                    ];
                }
            }
            if (Schema::hasTable('cms_pages')) {
                foreach (CmsPage::where('status', 'published')->orderBy('title')->get() as $page) {
                    if ($this->isNoindex($page->robots ?? null)) {
                        continue;
                    }
                    $groups['Pages'][] = [
                        'title' => $page->title,
                        'url' => route('page.show', $page->slug),
                    ];
                }
            }
        } catch (\Throwable $e) {
            // ignore missing tables during install
        }

        return view('sitemap.html', compact('groups'));
    }

    /** @return list<array{loc:string,lastmod:string,changefreq:string,priority:string}> */
    protected function collectUrls(): array
    {
        $now = now()->toAtomString();
        $urls = [
            ['loc' => url('/'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => url('/search'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.8'],
            ['loc' => url('/blog'), 'lastmod' => $now, 'changefreq' => 'daily', 'priority' => '0.7'],
            ['loc' => url('/sitemap'), 'lastmod' => $now, 'changefreq' => 'weekly', 'priority' => '0.3'],
        ];

        try {
            if (Schema::hasTable('categories')) {
                foreach (Category::orderBy('name')->get() as $c) {
                    if ($this->isNoindex($c->robots ?? null)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => route('category', $c->slug),
                        'lastmod' => optional($c->updated_at)->toAtomString() ?: $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.7',
                    ];
                }
            }
            if (Schema::hasTable('items')) {
                foreach (Item::approved()->get(['id', 'slug', 'updated_at', 'robots']) as $item) {
                    if ($this->isNoindex($item->robots ?? null)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => route('item.show', [$item->slug, $item->id]),
                        'lastmod' => optional($item->updated_at)->toAtomString() ?: $now,
                        'changefreq' => 'weekly',
                        'priority' => '0.9',
                    ];
                }
            }
            if (Schema::hasTable('blog_posts')) {
                foreach (BlogPost::where('status', 'published')->get() as $post) {
                    if ($this->isNoindex($post->robots ?? null)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => route('blog.show', $post->slug),
                        'lastmod' => optional($post->updated_at ?: $post->published_at)->toAtomString() ?: $now,
                        'changefreq' => 'monthly',
                        'priority' => '0.6',
                    ];
                }
            }
            if (Schema::hasTable('cms_pages')) {
                foreach (CmsPage::where('status', 'published')->get() as $page) {
                    if ($this->isNoindex($page->robots ?? null)) {
                        continue;
                    }
                    $urls[] = [
                        'loc' => route('page.show', $page->slug),
                        'lastmod' => optional($page->updated_at)->toAtomString() ?: $now,
                        'changefreq' => 'monthly',
                        'priority' => '0.5',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return $urls;
    }

    protected function isNoindex(?string $robots): bool
    {
        if (! $robots) {
            return false;
        }

        return str_contains(strtolower($robots), 'noindex');
    }
}
