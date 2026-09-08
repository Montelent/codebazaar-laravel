<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'description', 'attribute_schema',
        'seo_title', 'seo_description', 'canonical_url', 'robots',
    ];

    protected $casts = [
        'attribute_schema' => 'array',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /** @return array<int, self> */
    public function breadcrumbTrail(): array
    {
        $chain = [];
        $node = $this;
        $guard = 0;

        while ($node && $guard < 12) {
            array_unshift($chain, $node);
            if (! $node->relationLoaded('parent') && $node->parent_id) {
                $node->load('parent');
            }
            $node = $node->parent;
            $guard++;
        }

        return $chain;
    }

    public function seoPayload(): array
    {
        $desc = $this->seo_description
            ?: ($this->description
                ? Str::limit(strip_tags((string) $this->description), 160, '…')
                : 'Browse '.$this->name.' items');

        return [
            'title' => $this->seo_title ?: $this->name,
            'description' => $desc,
            'canonical' => $this->canonical_url ?: route('category', $this->slug),
            'robots' => $this->robots,
            'og_title' => $this->seo_title ?: $this->name,
            'og_description' => $desc,
            'og_type' => 'website',
        ];
    }
}
