<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WelcomeController extends Controller
{
    public function index()
    {
        // Ambil semua kategori
        $categories = Category::withCount('products')->get();

        // Ambil produk yang aktif, dengan limit untuk featured products
        $featuredProducts = Product::with('category')
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->take(8)
            ->get();

        // Ambil beberapa produk berdasarkan kategori
        $productsByCategory = [];
        foreach ($categories->take(4) as $category) {
            $productsByCategory[$category->id] = Product::with('category')
                ->where('category_id', $category->id)
                ->where('is_active', true)
                ->where('current_stock', '>', 0)
                ->take(4)
                ->get();
        }

        return Inertia::render('Welcome', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'productsByCategory' => $productsByCategory,
        ]);
    }
}
