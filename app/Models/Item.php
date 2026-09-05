<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'features', 'tags', 'attributes', 'gallery_urls',
        'regular_price', 'extended_price', 'sale_price_regular', 'sale_price_extended',
        'is_free', 'thumbnail_url', 'demo_url', 'main_file_url', 'status', 'is_featured',
        'sales_count', 'rating_avg', 'rating_count', 'author_id', 'category_id',
    ];

    protected $casts = [
        'features' => 'array',
        'tags' => 'array',
        'attributes' => 'array',
        'gallery_urls' => 'array',
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
}
