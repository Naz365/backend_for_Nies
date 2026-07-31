<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'logo_path',
        'website_url',
        'sort_order',
        'is_active',
    ];
}
