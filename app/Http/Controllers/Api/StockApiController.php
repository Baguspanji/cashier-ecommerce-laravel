<?php

namespace App\Http\Controllers\Api;

use App\Data\BulkStockAdjustmentData;
use App\Data\StockMovementData;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StockApiController extends Controller
{
    /**
     * Display a listing of stock movements.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('manage_stock');

        $movements = StockMovement::with(['product.category', 'user'])
            ->when($request->get('product_id'), function ($query, $productId) {
                $query->where('product_id', $productId);
            })
            ->when($request->get('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->get('date_from'), function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->get('date_to'), function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return StockMovementResource::collection($movements);
    }

    /**
     * Store a newly created stock movement.
     */
    public function store(StockMovementData $data): JsonResponse
    {
        Gate::authorize('manage_stock');

        $product = Product::findOrFail($data->product_id);

        // Create stock movement
        $movement = StockMovement::createMovement(
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

        return response()->json([
            'message' => "Stok {$typeText} berhasil dicatat.",
            'data' => new StockMovementResource($movement->load(['product.category', 'user'])),
        ], 201);
    }

    /**
     * Display stock overview/summary.
     */
    public function overview(Request $request): JsonResponse
    {
        Gate::authorize('manage_stock');

        $products = Product::with('category')
            ->when($request->get('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->get('stock_status'), function ($query, $status) {
                if ($status === 'low') {
                    $query->whereRaw('current_stock <= minimum_stock');
                } elseif ($status === 'out') {
                    $query->where('current_stock', 0);
                }
            })
            ->orderBy('name')
            ->paginate($request->get('per_page', 20));

        $summary = [
            'total_products' => Product::count(),
            'low_stock_count' => Product::whereRaw('current_stock <= minimum_stock')->count(),
            'out_of_stock_count' => Product::where('current_stock', 0)->count(),
            'total_stock_value' => Product::selectRaw('SUM(current_stock * price) as total')
                ->value('total') ?? 0,
        ];

        $productsCollection = ProductResource::collection($products);

        return response()->json([
            'products' => $productsCollection->response()->getData(true),
            'summary' => $summary,
        ]);
    }

    /**
     * Show stock movements for a specific product.
     */
    public function productMovements(Product $product, Request $request): JsonResponse
    {
        Gate::authorize('manage_stock');

        $movements = $product->stockMovements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        $movementsCollection = StockMovementResource::collection($movements);

        return response()->json([
            'product' => new ProductResource($product->load('category')),
            'movements' => $movementsCollection->response()->getData(true),
        ]);
    }

    /**
     * Bulk stock adjustment.
     */
    public function bulkAdjustment(BulkStockAdjustmentData $data): JsonResponse
    {
        Gate::authorize('manage_stock');

        $processedAdjustments = [];

        foreach ($data->adjustments as $adjustment) {
            $product = Product::findOrFail($adjustment['product_id']);
            $currentStock = $product->current_stock;
            $newStock = $adjustment['new_stock'];

            if ($currentStock !== $newStock) {
                $quantity = abs($newStock - $currentStock);
                $type = $newStock > $currentStock ? 'in' : 'out';

                $movement = StockMovement::createMovement(
                    $product,
                    $type,
                    $quantity,
                    notes: $data->notes ?: 'Penyesuaian stok massal',
                    userId: Auth::id()
                );

                $processedAdjustments[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_stock' => $currentStock,
                    'new_stock' => $newStock,
                    'type' => $type,
                    'quantity' => $quantity,
                    'movement_id' => $movement->id,
                ];
            }
        }

        return response()->json([
            'message' => 'Penyesuaian stok massal berhasil dilakukan.',
            'processed_adjustments' => $processedAdjustments,
            'total_processed' => count($processedAdjustments),
        ]);
    }

    /**
     * Get products with low stock or out of stock.
     */
    public function alerts(Request $request): JsonResponse
    {
        Gate::authorize('manage_stock');

        $lowStock = Product::with('category')
            ->whereRaw('current_stock <= minimum_stock')
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();

        $outOfStock = Product::with('category')
            ->where('current_stock', 0)
            ->orderBy('name')
            ->get();

        return response()->json([
            'low_stock' => ProductResource::collection($lowStock),
            'out_of_stock' => ProductResource::collection($outOfStock),
            'counts' => [
                'low_stock' => $lowStock->count(),
                'out_of_stock' => $outOfStock->count(),
            ],
        ]);
    }
}
