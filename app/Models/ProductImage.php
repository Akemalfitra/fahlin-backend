<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'label',
        'description',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::saved(function (ProductImage $productImage): void {
            $productImage->syncProductPrimaryImage();
        });

        static::deleted(function (ProductImage $productImage): void {
            $productImage->syncProductPrimaryImage();
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    private function syncProductPrimaryImage(): void
    {
        $product = $this->product()->first();

        if (! $product) {
            return;
        }

        $primaryImage = $product->productImages()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->value('image_path');

        if ($primaryImage && $product->image !== $primaryImage) {
            $product->forceFill(['image' => $primaryImage])->saveQuietly();
        }
    }
}
