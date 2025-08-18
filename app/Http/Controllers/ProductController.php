<?php

namespace App\Http\Controllers;

use App\Data\CategoryData;
use App\Data\ProductData;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index()
    {
        $products = Product::with('category')
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
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
        $product = Product::create($data->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
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
        $product->update($data->toArray());

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        // Check if product has transaction items
        if ($product->transactionItems()->count() > 0) {
            return redirect()->route('products.index')
                ->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi.');
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Toggle product status (active/inactive).
     */
    public function toggleStatus(Product $product)
    {
        $product->update(['is_active' => ! $product->is_active]);

        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Produk berhasil {$status}.");
    }
}
