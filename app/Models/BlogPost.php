<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'cover_url', 'status',
        'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'robots', 'focus_keyword',
        'category', 'author_id', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function seoPayload(): array
    {
        $desc = $this->seo_description
            ?: ($this->excerpt ?: Str::limit(strip_tags((string) $this->content), 160, '…'));

        return [
            'title' => $this->seo_title ?: $this->title,
            'description' => $desc,
            'keywords' => $this->seo_keywords,
            'canonical' => $this->canonical_url ?: route('blog.show', $this->slug),
            'robots' => $this->robots,
            'og_title' => $this->og_title ?: ($this->seo_title ?: $this->title),
            'og_description' => $this->og_description ?: $desc,
            'og_image' => $this->og_image ?: $this->cover_url,
            'og_type' => 'article',
        ];
    }
}
