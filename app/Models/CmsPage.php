<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $fillable = [
        'title', 'slug', 'content', 'status', 'seo_title', 'seo_description',
    ];
}
