<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuoteRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'customer_name',
        'company_name',
        'email',
        'phone',
        'service_type',
        'project_description',
        'status',
        'notes',
    ];

    public static function generateRequestNumber(): string
    {
        return 'QR-' . date('Y') . '-' . str_pad((string) (static::whereYear('created_at', date('Y'))->count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
