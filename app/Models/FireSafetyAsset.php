<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FireSafetyAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_tag',
        'serial_number',
        'customer_id',
        'product_id',
        'asset_name',
        'equipment_type',
        'location_in_building',
        'capacity_rating',
        'installed_date',
        'last_serviced_date',
        'next_service_due_date',
        'status',
        'qr_code_token',
        'notes',
    ];

    protected $casts = [
        'installed_date' => 'date',
        'last_serviced_date' => 'date',
        'next_service_due_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public static function generateAssetTag(): string
    {
        do {
            $suffix = strtoupper(Str::random(6));
            $candidate = 'AST-' . date('Y') . '-' . $suffix;
        } while (static::where('asset_tag', $candidate)->exists());

        return $candidate;
    }
}
