<?php

namespace App\Http\Controllers\Api;

use App\Data\CategoryData;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CategoryApiController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage_categories');

        $categories = Category::withCount('products')
            ->when($request->get('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return CategoryResource::collection($categories);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryData $data): JsonResponse
    {
        Gate::authorize('manage_categories');

        $category = Category::create($data->toArray());

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => new CategoryResource($category),
        ], 201);
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): CategoryResource
    {
        Gate::authorize('manage_categories');

        return new CategoryResource($category->loadCount('products'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(CategoryData $data, Category $category): JsonResponse
    {
        Gate::authorize('manage_categories');

        $category->update($data->toArray());

        return response()->json([
            'message' => 'Kategori berhasil diperbarui.',
            'data' => new CategoryResource($category->fresh()),
        ]);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        Gate::authorize('manage_categories');

        // Check if category has products
        if ($category->products()->count() > 0) {
            return response()->json([
                'message' => 'Kategori tidak dapat dihapus karena masih memiliki produk.',
                'error' => 'has_products',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
