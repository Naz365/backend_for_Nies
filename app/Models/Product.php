<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (empty($product->category_slug) && $product->category_id) {
                $category = $product->category ?? Category::find($product->category_id);
                if ($category) {
                    $product->category_slug = $category->slug;
                    $product->category_name = $category->name;
                }
            }
            if (empty($product->slug) && !empty($product->title)) {
                $product->slug = \Illuminate\Support\Str::slug($product->title);
            }
        });
    }

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'sku',
        'category_slug',
        'category_name',
        'image',
        'description',
        'specifications',
        'price',
        'compare_at_price',
        'stock_quantity',
        'track_inventory',
        'is_featured',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_inventory' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isInStock(): bool
    {
        return !$this->track_inventory || $this->stock_quantity > 0;
    }
}
