<?php

namespace App\Support;

use App\Models\SiteSetting;

class AdSlots
{
    /** @return array<string, array{label:string,group:string,help:string}> */
    public static function definitions(): array
    {
        return [
            'sitewide_after_header' => [
                'label' => 'After header (all pages)',
                'group' => 'Sitewide',
                'help' => 'Below the navigation, above main content.',
            ],
            'sitewide_before_footer' => [
                'label' => 'Before footer (all pages)',
                'group' => 'Sitewide',
                'help' => 'Above the site footer.',
            ],
            'footer_top' => [
                'label' => 'Footer — top',
                'group' => 'Footer',
                'help' => 'Inside the footer, above columns.',
            ],
            'footer_bottom' => [
                'label' => 'Footer — bottom',
                'group' => 'Footer',
                'help' => 'Above the copyright line.',
            ],
            'homepage_after_hero' => [
                'label' => 'Homepage — after hero',
                'group' => 'Homepage',
                'help' => 'Directly under the search hero.',
            ],
            'homepage_before_blog' => [
                'label' => 'Homepage — before blog',
                'group' => 'Homepage',
                'help' => 'Above the “From the blog” section.',
            ],
            'blog_index_top' => [
                'label' => 'Blog list — top',
                'group' => 'Blog',
                'help' => 'Top of /blog listing.',
            ],
            'blog_index_bottom' => [
                'label' => 'Blog list — bottom',
                'group' => 'Blog',
                'help' => 'Bottom of /blog listing.',
            ],
            'blog_post_before' => [
                'label' => 'Blog post — beginning',
                'group' => 'Blog',
                'help' => 'Above the post title.',
            ],
            'blog_post_after_title' => [
                'label' => 'Blog post — after title',
                'group' => 'Blog',
                'help' => 'After title / date, before content.',
            ],
            'blog_post_middle' => [
                'label' => 'Blog post — middle (after N paragraphs)',
                'group' => 'Blog',
                'help' => 'Inserted after the Nth paragraph in the body. Set “Middle paragraph index” below.',
            ],
            'blog_post_end' => [
                'label' => 'Blog post — end of content',
                'group' => 'Blog',
                'help' => 'After the last paragraph of the post.',
            ],
            'blog_post_after' => [
                'label' => 'Blog post — after post',
                'group' => 'Blog',
                'help' => 'Below the article block.',
            ],
            'product_before' => [
                'label' => 'Product — beginning',
                'group' => 'Product',
                'help' => 'Above the product title / breadcrumb area.',
            ],
            'product_after_title' => [
                'label' => 'Product — after title',
                'group' => 'Product',
                'help' => 'Under title/meta, above the preview gallery.',
            ],
            'product_before_description' => [
                'label' => 'Product — before description',
                'group' => 'Product',
                'help' => 'Start of the Item details tab.',
            ],
            'product_middle_description' => [
                'label' => 'Product — middle of description',
                'group' => 'Product',
                'help' => 'After N paragraphs inside the description HTML.',
            ],
            'product_after_description' => [
                'label' => 'Product — end of description',
                'group' => 'Product',
                'help' => 'After description, before features/tags.',
            ],
            'product_sidebar' => [
                'label' => 'Product — sidebar',
                'group' => 'Product',
                'help' => 'Under the buy box / attributes sidebar.',
            ],
            'product_after' => [
                'label' => 'Product — after content',
                'group' => 'Product',
                'help' => 'Below the product grid, above related items.',
            ],
            'search_top' => [
                'label' => 'Search / catalog — top',
                'group' => 'Catalog',
                'help' => 'Top of search results page.',
            ],
            'category_top' => [
                'label' => 'Category — top',
                'group' => 'Catalog',
                'help' => 'Top of category listing pages.',
            ],
        ];
    }

    public static function config(): array
    {
        $defaults = [
            'enabled' => true,
            'middle_paragraph' => 3,
            'slots' => [],
        ];
        foreach (array_keys(self::definitions()) as $key) {
            $defaults['slots'][$key] = ['enabled' => false, 'html' => ''];
        }

        $stored = SiteSetting::getValue('ad_placements', []);
        if (! is_array($stored)) {
            return $defaults;
        }

        $out = array_merge($defaults, $stored);
        $out['slots'] = array_merge($defaults['slots'], is_array($stored['slots'] ?? null) ? $stored['slots'] : []);

        return $out;
    }

    public static function html(string $slot): string
    {
        $cfg = self::config();
        if (empty($cfg['enabled'])) {
            return '';
        }
        $row = $cfg['slots'][$slot] ?? null;
        if (! is_array($row) || empty($row['enabled'])) {
            return '';
        }
        $html = trim((string) ($row['html'] ?? ''));

        return $html;
    }

    public static function render(string $slot): string
    {
        $html = self::html($slot);
        if ($html === '') {
            return '';
        }

        return '<div class="cc-ad-slot cc-ad-slot--'.e($slot).' my-4" data-ad-slot="'.e($slot).'">'.$html.'</div>';
    }

    /**
     * Insert ad HTML after the Nth closing </p> in content (1-based).
     */
    public static function injectAfterParagraphs(string $html, string $slot): string
    {
        $ad = self::html($slot);
        if ($ad === '' || $html === '') {
            return $html;
        }

        $cfg = self::config();
        $n = max(1, (int) ($cfg['middle_paragraph'] ?? 3));
        $count = 0;
        $result = preg_replace_callback('/<\/p\s*>/i', function ($m) use (&$count, $n, $ad) {
            $count++;
            if ($count === $n) {
                return $m[0]."\n".'<div class="cc-ad-slot cc-ad-slot-inline my-4" data-ad-slot="inline">'.$ad.'</div>';
            }

            return $m[0];
        }, $html, $n);

        // If fewer than N paragraphs, append at end
        if ($count < $n) {
            $result .= "\n".'<div class="cc-ad-slot cc-ad-slot-inline my-4">'.$ad.'</div>';
        }

        return $result ?? $html;
    }
}
