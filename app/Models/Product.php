<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $table = 'sv23810310303_products';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'stock_quantity',
        'image_path',
        'status',
        'discount_percent',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'discount_percent' => 'integer',
    ];

    protected $appends = [
        'discounted_price',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function getDiscountedPriceAttribute(): float
    {
        $price = (float) $this->price;
        $discountPercent = max(0, min(100, (int) $this->discount_percent));

        return round($price - ($price * $discountPercent / 100), 2);
    }
}
