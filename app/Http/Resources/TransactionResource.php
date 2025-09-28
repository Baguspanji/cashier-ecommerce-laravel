<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'transaction_number' => $this->transaction_number,
            'user_id' => $this->user_id,
            'customer_name' => $this->customer_name,
            'total_amount' => (float) $this->total_amount,
            'payment_method' => $this->payment_method,
            'payment_amount' => (float) $this->payment_amount,
            'change_amount' => (float) $this->change_amount,
            'income' => (float) $this->income,
            'status' => $this->status,
            'notes' => $this->notes,
            'offline_id' => $this->offline_id,
            'sync_status' => $this->sync_status,
            'last_sync_at' => $this->last_sync_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),

            // Relationships
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => (float) $item->price,
                        'subtotal' => (float) $item->subtotal,
                        'income' => (float) $item->income,
                        'product' => $this->when($item->relationLoaded('product'), function () use ($item) {
                            return [
                                'id' => $item->product->id,
                                'name' => $item->product->name,
                                'barcode' => $item->product->barcode,
                                'price' => (float) $item->product->price,
                                'price_purchase' => (float) $item->product->price_purchase,
                                'current_stock' => $item->product->current_stock,
                                'minimum_stock' => $item->product->minimum_stock,
                                'is_active' => $item->product->is_active,
                                'category' => $this->when($item->product->relationLoaded('category'), function () use ($item) {
                                    return [
                                        'id' => $item->product->category->id,
                                        'name' => $item->product->category->name,
                                        'is_active' => $item->product->category->is_active,
                                    ];
                                }),
                            ];
                        }),
                    ];
                });
            }),

            // Summary calculations
            'items_count' => $this->whenLoaded('items', function () {
                return $this->items->count();
            }),

            'total_quantity' => $this->whenLoaded('items', function () {
                return $this->items->sum('quantity');
            }),
        ];
    }
}
