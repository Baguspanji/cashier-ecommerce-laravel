<?php

namespace App\Http\Controllers\Api;

use App\Data\ProductData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class ProductApiController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage_products');

        $products = Product::with('category')
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->when($request->get('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->get('status'), function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(ProductData $data): JsonResponse
    {
        Gate::authorize('manage_products');

        $product = Product::create($data->toArray());

        return response()->json([
            'message' => 'Produk berhasil ditambahkan.',
            'data' => new ProductResource($product->load('category')),
        ], 201);
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product): ProductResource
    {
        Gate::authorize('manage_products');

        return new ProductResource($product->load('category'));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(ProductData $data, Product $product): JsonResponse
    {
        Gate::authorize('manage_products');

        $product->update($data->toArray());

        return response()->json([
            'message' => 'Produk berhasil diperbarui.',
            'data' => new ProductResource($product->fresh(['category'])),
        ]);
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('manage_products');

        // Check if product has transaction history
        if ($product->transactionItems()->exists()) {
            return response()->json([
                'message' => 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.',
                'error' => 'has_transactions',
            ], 422);
        }

        $product->delete();

        return response()->json([
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    /**
     * Search product by barcode.
     */
    public function searchByBarcode(string $barcode): JsonResponse
    {
        Gate::authorize('manage_products');

        $product = Product::findByBarcode($barcode);

        if ($product) {
            return response()->json([
                'success' => true,
                'data' => new ProductResource($product->load('category')),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found',
        ], 404);
    }

    /**
     * Toggle product status (active/inactive).
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        Gate::authorize('manage_products');

        $product->update(['is_active' => ! $product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return response()->json([
            'message' => "Produk berhasil {$status}.",
            'data' => new ProductResource($product->load('category')),
        ]);
    }
}
