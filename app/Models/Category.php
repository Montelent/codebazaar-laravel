<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'parent_id', 'description', 'attribute_schema'];

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

    /**
     * Full ancestor chain root → this category (CodeCanyon-style breadcrumb).
     *
     * @return array<int, self>
     */
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
}
