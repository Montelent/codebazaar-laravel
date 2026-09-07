<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'features', 'tags', 'attributes', 'gallery_urls',
        'regular_price', 'extended_price', 'sale_price_regular', 'sale_price_extended',
        'is_free', 'thumbnail_url', 'demo_url', 'main_file_url', 'download_files',
        'status', 'is_featured', 'sales_count', 'rating_avg', 'rating_count',
        'author_id', 'category_id',
        'seo_title', 'seo_description', 'seo_keywords', 'canonical_url',
        'og_title', 'og_description', 'og_image', 'robots', 'focus_keyword',
    ];

    protected $casts = [
        'features' => 'array',
        'tags' => 'array',
        'attributes' => 'array',
        'gallery_urls' => 'array',
        'download_files' => 'array',
        'is_free' => 'boolean',
        'is_featured' => 'boolean',
        'regular_price' => 'decimal:2',
        'extended_price' => 'decimal:2',
        'sale_price_regular' => 'decimal:2',
        'sale_price_extended' => 'decimal:2',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function effectiveRegularPrice(): float
    {
        if ($this->is_free || (float) $this->regular_price <= 0) {
            return 0.0;
        }
        if ($this->sale_price_regular !== null) {
            return (float) $this->sale_price_regular;
        }

        return (float) $this->regular_price;
    }

    public function effectiveExtendedPrice(): float
    {
        if ($this->is_free || (float) $this->regular_price <= 0) {
            return 0.0;
        }
        if ($this->sale_price_extended !== null) {
            return (float) $this->sale_price_extended;
        }

        return (float) $this->extended_price;
    }

    public function downloadFilesList(): array
    {
        $files = is_array($this->download_files) ? $this->download_files : [];
        $out = [];

        foreach ($files as $row) {
            if (! is_array($row)) {
                continue;
            }
            $url = trim((string) ($row['url'] ?? ''));
            if ($url === '') {
                continue;
            }
            $out[] = [
                'label' => trim((string) ($row['label'] ?? '')) ?: 'Download',
                'url' => $url,
                'type' => in_array(($row['type'] ?? 'main'), ['main', 'addon', 'extra'], true)
                    ? $row['type']
                    : 'main',
            ];
        }

        if (count($out) === 0 && ! empty($this->main_file_url)) {
            $out[] = [
                'label' => 'Main file',
                'url' => $this->main_file_url,
                'type' => 'main',
            ];
        }

        return $out;
    }

    public function primaryDownloadUrl(): ?string
    {
        $list = $this->downloadFilesList();

        return $list[0]['url'] ?? null;
    }

    public function seoPayload(): array
    {
        $desc = $this->seo_description
            ?: \Illuminate\Support\Str::limit(strip_tags((string) $this->description), 160, '…');

        return [
            'title' => $this->seo_title ?: $this->title,
            'description' => $desc,
            'keywords' => $this->seo_keywords,
            'canonical' => $this->canonical_url ?: route('item.show', [$this->slug, $this->id]),
            'robots' => $this->robots,
            'og_title' => $this->og_title ?: ($this->seo_title ?: $this->title),
            'og_description' => $this->og_description ?: $desc,
            'og_image' => $this->og_image ?: $this->thumbnail_url,
            'og_type' => 'product',
        ];
    }
}
