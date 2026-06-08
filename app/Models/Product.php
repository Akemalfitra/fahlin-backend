<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'price',
        'image',
        'images',
        'description',
        'stock',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    public function getImagesAttribute($value): array
    {
        if ($this->relationLoaded('productImages') && $this->productImages->isNotEmpty()) {
            return $this->productImages
                ->pluck('image_path')
                ->filter()
                ->values()
                ->all();
        }

        $images = is_array($value)
            ? $value
            : json_decode($value ?? '[]', true);

        if (! empty($images)) {
            return array_values(array_filter($images));
        }

        return $this->image ? [$this->image] : [];
    }

    public function setImagesAttribute($value): void
    {
        $images = array_values(array_filter((array) $value));

        $this->attributes['images'] = json_encode($images);

        if (! empty($images)) {
            $this->attributes['image'] = $images[0];
        }
    }
}
