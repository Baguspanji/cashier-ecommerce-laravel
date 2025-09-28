<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'barcode' => $this->barcode,
            'description' => $this->description,
            'price' => $this->price,
            'price_purchase' => $this->price_purchase,
            'image_path' => $this->image_path,
            'category_id' => $this->category_id,
            'current_stock' => $this->current_stock,
            'minimum_stock' => $this->minimum_stock,
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded('category', fn() => new CategoryResource($this->category)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
