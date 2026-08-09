<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'balance_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'balance_after' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Record an inventory transaction and adjust product stock balance atomically
     */
    public static function record(Product $product, string $type, int $qty, ?string $notes = null, $reference = null, ?int $userId = null): self
    {
        $newBalance = $product->stock_quantity + $qty;
        if ($newBalance < 0) {
            throw new \InvalidArgumentException("Inventory balance cannot be negative for product '{$product->title}'.");
        }

        $product->update(['stock_quantity' => $newBalance]);

        return self::create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $qty,
            'balance_after' => $newBalance,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference ? $reference->id : null,
            'notes' => $notes,
            'created_by' => $userId,
        ]);
    }
}
