<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public static function generateOrderNumber(): string
    {
        do {
            $randomSuffix = strtoupper(Str::random(6));
            $candidate = 'NIES-' . date('Ymd') . '-' . $randomSuffix;
        } while (static::where('order_number', $candidate)->exists());

        return $candidate;
    }
}
