<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'address',
        'phone_primary',
        'phone_secondary',
        'telephone',
        'fax',
        'emails',
        'company_profile_pdf',
    ];

    protected $casts = [
        'emails' => 'array',
    ];
}
