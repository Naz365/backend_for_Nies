<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'category_slug',
        'category_name',
        'image',
        'description',
        'specifications',
        'is_featured',
        'status',
        'meta_title',
        'meta_description',
    ];
}
