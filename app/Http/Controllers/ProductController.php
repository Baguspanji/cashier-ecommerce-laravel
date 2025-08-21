<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Data\ProductData;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        Gate::authorize('manage_products');

        $products = Product::with('category')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->when(request('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(request('status'), function ($query, $status) {
                if ($status === 'active') {
                    $query->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $query->where('is_active', false);
                }
            })
            ->orderBy('name')
            ->paginate(12);

        $categories = Category::orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => $this->mapPagination($products, fn ($items) => ProductData::collect($items)),
            'categories' => CategoryData::collect($categories),
            'filters' => request()->only(['search', 'category_id', 'status']),
        ]);
    }

    /**
     * Show the form for creating a new product.
     */
    public function create()
    {
        Gate::authorize('manage_products');

        $categories = Category::orderBy('name')->get();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(ProductData $data)
    {
        Gate::authorize('manage_products');

        // Create product first to get ID
        $productData = $data->toArray();
        $product = Product::create($productData);

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        Gate::authorize('manage_products');

        $product->load('category', 'transactionItems', 'stockMovements.user');

        return Inertia::render('Products/Show', [
            'product' => ProductData::from($product),
        ]);
    }

    /**
     * Show the form for editing the specified product.
     */
    public function edit(Product $product)
    {
        Gate::authorize('manage_products');

        $categories = Category::orderBy('name')->get();

        return Inertia::render('Products/Edit', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified product in storage.
     */
    public function update(ProductData $data, Product $product)
    {
        Gate::authorize('manage_products');

        $product->update($data->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

        /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        Gate::authorize('manage_products');

        // Check if product has transaction history
        if ($product->transactionItems()->exists()) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Search product by barcode for POS
     */
    public function searchByBarcode(string $barcode)
    {
        Gate::authorize('manage_products');

        $product = Product::findByBarcode($barcode);

        if ($product) {
            return response()->json([
                'success' => true,
                'product' => ProductData::from($product->load('category'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found'
        ], 404);
    }

    /**
     * Toggle product status (active/inactive).
     */
    public function toggleStatus(Product $product)
    {
        Gate::authorize('manage_products');

        $product->update(['is_active' => ! $product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Produk berhasil {$status}.");
    }
}
