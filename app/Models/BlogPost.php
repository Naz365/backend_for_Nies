<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'thumbnail',
        'author',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
    ];
}
