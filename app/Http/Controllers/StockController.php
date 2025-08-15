<?php

namespace App\Http\Controllers;

use App\Data\BulkStockAdjustmentData;
use App\Data\StockMovementData;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StockController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index()
    {
        $movements = StockMovement::with(['product', 'user'])
            ->when(request('product_id'), function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when(request('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when(request('date_from'), function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when(request('date_to'), function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $products = Product::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Stock/Index', [
            'movements' => $movements,
            'products' => $products,
            'filters' => request()->only(['product_id', 'type', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new stock adjustment.
     */
    public function create()
    {
        $products = Product::orderBy('name')->get(['id', 'name', 'current_stock']);

        return Inertia::render('Stock/Create', [
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created stock adjustment.
     */
    public function store(StockMovementData $data)
    {
        $product = Product::findOrFail($data->product_id);

        // For 'out' movements, check if sufficient stock is available
        if ($data->type === 'out' && $product->current_stock < $data->quantity) {
            return redirect()->back()
                ->withErrors(['quantity' => 'Stok tidak mencukupi untuk pengurangan ini.']);
        }

        // Create stock movement
        StockMovement::createMovement(
            $product,
            $data->type,
            $data->quantity,
            notes: $data->notes,
            userId: Auth::id()
        );

        $typeText = match ($data->type) {
            'in' => 'penambahan',
            'out' => 'pengurangan',
            'adjustment' => 'penyesuaian',
        };

        return redirect()->route('stock.index')
            ->with('success', "Stok {$typeText} berhasil dicatat.");
    }

    /**
     * Display stock overview/summary.
     */
    public function overview()
    {
        $products = Product::with('category')
            ->when(request('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(request('stock_status'), function ($query, $status) {
                if ($status === 'low') {
                    $query->whereRaw('current_stock <= minimum_stock');
                } elseif ($status === 'out') {
                    $query->where('current_stock', 0);
                }
            })
            ->orderBy('name')
            ->paginate(20);

        $categories = \App\Models\Category::orderBy('name')->get(['id', 'name']);

        $summary = [
            'total_products' => Product::count(),
            'low_stock_count' => Product::whereRaw('current_stock <= minimum_stock')->count(),
            'out_of_stock_count' => Product::where('current_stock', 0)->count(),
            'total_stock_value' => Product::selectRaw('SUM(current_stock * price) as total')
                ->value('total') ?? 0,
        ];

        return Inertia::render('Stock/Overview', [
            'products' => $products,
            'categories' => $categories,
            'summary' => $summary,
            'filters' => request()->only(['category_id', 'stock_status']),
        ]);
    }

    /**
     * Show stock movements for a specific product.
     */
    public function productMovements(Product $product)
    {
        $movements = $product->stockMovements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Stock/ProductMovements', [
            'product' => $product,
            'movements' => $movements,
        ]);
    }

    /**
     * Bulk stock adjustment.
     */
    public function bulkAdjustment(BulkStockAdjustmentData $data)
    {
        foreach ($data->adjustments as $adjustment) {
            $product = Product::findOrFail($adjustment['product_id']);
            $currentStock = $product->current_stock;
            $newStock = $adjustment['new_stock'];

            if ($currentStock !== $newStock) {
                $quantity = abs($newStock - $currentStock);
                $type = $newStock > $currentStock ? 'in' : 'out';

                StockMovement::createMovement(
                    $product,
                    $type,
                    $quantity,
                    notes: $data->notes ?: 'Penyesuaian stok massal',
                    userId: Auth::id()
                );
            }
        }

        return redirect()->route('stock.overview')
            ->with('success', 'Penyesuaian stok massal berhasil dilakukan.');
    }
}
