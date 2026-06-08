<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category' => $this->whenLoaded('category', fn (): ?array => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : null),
            'name' => $this->name,
            'price' => $this->price,
            'image' => $this->image,
            'images' => $this->images,
            'image_options' => $this->whenLoaded('productImages', fn (): array => $this->productImages
                ->map(fn ($image): array => [
                    'id' => $image->id,
                    'image' => $image->image_path,
                    'label' => $image->label,
                    'description' => $image->description,
                    'sort_order' => $image->sort_order,
                ])
                ->values()
                ->all()),
            'description' => $this->description,
            'stock' => $this->stock,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
