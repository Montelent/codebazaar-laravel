<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CmsPage extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'status',
        'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'robots', 'focus_keyword',
    ];

    public function seoPayload(): array
    {
        $desc = $this->seo_description
            ?: Str::limit(strip_tags((string) $this->content), 160, '…');

        return [
            'title' => $this->seo_title ?: $this->title,
            'description' => $desc,
            'keywords' => $this->seo_keywords,
            'canonical' => $this->canonical_url ?: route('page.show', $this->slug),
            'robots' => $this->robots,
            'og_title' => $this->og_title ?: ($this->seo_title ?: $this->title),
            'og_description' => $this->og_description ?: $desc,
            'og_image' => $this->og_image,
            'og_type' => 'website',
        ];
    }
}
