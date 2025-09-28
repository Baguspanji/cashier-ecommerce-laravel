<?php

namespace App\Http\Controllers\Api;

use App\Data\CreateTransactionData;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class TransactionApiController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('view_reports');

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'period' => ['nullable', 'in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom'],
            'payment_method' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $period = $request->get('period', 'this_month');
        $dateRange = $this->getDateRange($period, $request->start_date, $request->end_date);

        $transactions = Transaction::with(['user', 'items.product.category'])
            ->whereBetween('created_at', $dateRange)
            ->when($request->payment_method, function ($query, $paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('transaction_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'data' => TransactionResource::collection($transactions)->response()->getData(true)['data'],
            'links' => $transactions->linkCollection(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Store a newly created transaction.
     */
    public function store(CreateTransactionData $data): JsonResponse
    {
        Gate::authorize('manage_transactions');

        try {
            $transaction = DB::transaction(function () use ($data) {
                // Generate transaction number
                $transactionNumber = Transaction::generateTransactionNumber();

                // Calculate totals
                $totalAmount = 0;
                $totalIncome = 0;
                $items = [];

                foreach ($data->items as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    // Check stock availability
                    if ($product->current_stock < $item['quantity']) {
                        throw new \Exception("Insufficient stock for product: {$product->name}. Available: {$product->current_stock}, Required: {$item['quantity']}");
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $purchaseCost = $product->price_purchase * $item['quantity'];
                    $itemIncome = $subtotal - $purchaseCost;

                    $totalAmount += $subtotal;
                    $totalIncome += $itemIncome;

                    $items[] = [
                        'product' => $product,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'subtotal' => $subtotal,
                        'income' => $itemIncome,
                    ];
                }

                // Calculate change
                $changeAmount = max(0, $data->payment_amount - $totalAmount);

                // Validate payment amount
                if ($data->payment_amount < $totalAmount) {
                    throw new \Exception('Payment amount is insufficient');
                }

                // Create transaction
                $transaction = Transaction::create([
                    'transaction_number' => $transactionNumber,
                    'user_id' => Auth::id(),
                    'total_amount' => $totalAmount,
                    'payment_method' => $data->payment_method,
                    'payment_amount' => $data->payment_amount,
                    'change_amount' => $changeAmount,
                    'income' => $totalIncome,
                    'status' => 'completed',
                    'notes' => $data->notes,
                ]);

                // Create transaction items and update stock
                foreach ($items as $item) {
                    // Create transaction item
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product']->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'price_purchase' => $item['product']->price_purchase,
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update product stock and create movement
                    $previousStock = $item['product']->current_stock;
                    $item['product']->decrement('current_stock', $item['quantity']);
                    $newStock = $item['product']->fresh()->current_stock;

                    // Create stock movement
                    StockMovement::create([
                        'product_id' => $item['product']->id,
                        'type' => 'out',
                        'quantity' => $item['quantity'],
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'reference_id' => $transaction->id,
                        'reference_type' => Transaction::class,
                        'notes' => 'Sale - Transaction #'.$transactionNumber,
                        'user_id' => Auth::id(),
                    ]);
                }

                return $transaction->load(['user', 'items.product.category']);
            });

            return response()->json([
                'message' => 'Transaction created successfully.',
                'data' => new TransactionResource($transaction),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Transaction failed: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        Gate::authorize('view_reports');

        $transaction->load(['user', 'items.product.category']);

        return response()->json([
            'data' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Cancel the specified transaction.
     */
    public function cancel(Transaction $transaction): JsonResponse
    {
        Gate::authorize('manage_transactions');

        if ($transaction->status === 'cancelled') {
            return response()->json([
                'message' => 'Transaction is already cancelled.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($transaction) {
                // Restore stock for each item
                foreach ($transaction->items as $item) {
                    $previousStock = $item->product->current_stock;
                    $item->product->increment('current_stock', $item->quantity);
                    $newStock = $item->product->fresh()->current_stock;

                    // Create stock movement for restoration
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'type' => 'in',
                        'quantity' => $item->quantity,
                        'previous_stock' => $previousStock,
                        'new_stock' => $newStock,
                        'reference_id' => $transaction->id,
                        'reference_type' => Transaction::class,
                        'notes' => 'Cancelled Transaction - #'.$transaction->transaction_number,
                        'user_id' => Auth::id(),
                    ]);
                }

                // Update transaction status
                $transaction->update(['status' => 'cancelled']);
            });

            return response()->json([
                'message' => 'Transaction cancelled successfully.',
                'data' => new TransactionResource($transaction->load(['user', 'items.product.category'])),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel transaction: '.$e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get daily report summary.
     */
    public function dailyReport(Request $request): JsonResponse
    {
        Gate::authorize('view_reports');

        $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = $request->get('date', now()->toDateString());
        $dateRange = [
            Carbon::parse($date)->startOfDay(),
            Carbon::parse($date)->endOfDay(),
        ];

        $transactions = Transaction::whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->with(['items.product.category'])
            ->get();

        $summary = [
            'date' => $date,
            'total_transactions' => $transactions->count(),
            'total_revenue' => $transactions->sum('total_amount'),
            'total_income' => $transactions->sum('income'),
            'payment_methods' => $transactions->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                ];
            }),
            'top_products' => $this->getTopProducts($transactions),
            'hourly_sales' => $this->getHourlySales($transactions),
        ];

        return response()->json([
            'data' => $summary,
            'transactions' => TransactionResource::collection($transactions),
        ]);
    }

    /**
     * Get sales analytics.
     */
    public function analytics(Request $request): JsonResponse
    {
        Gate::authorize('view_reports');

        $request->validate([
            'period' => ['nullable', 'in:7days,30days,90days,1year'],
            'group_by' => ['nullable', 'in:day,week,month'],
        ]);

        $period = $request->get('period', '30days');
        $groupBy = $request->get('group_by', 'day');

        $dateRange = $this->getAnalyticsDateRange($period);

        $transactions = Transaction::whereBetween('created_at', $dateRange)
            ->where('status', 'completed')
            ->with(['items.product.category'])
            ->get();

        $analytics = [
            'period' => $period,
            'group_by' => $groupBy,
            'total_revenue' => $transactions->sum('total_amount'),
            'total_income' => $transactions->sum('income'),
            'total_transactions' => $transactions->count(),
            'average_transaction_value' => $transactions->count() > 0 ? $transactions->avg('total_amount') : 0,
            'sales_trend' => $this->getSalesTrend($transactions, $groupBy),
            'payment_methods_breakdown' => $transactions->groupBy('payment_method')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                    'percentage' => 0, // Will be calculated below
                ];
            }),
            'top_products' => $this->getTopProducts($transactions, 10),
            'top_categories' => $this->getTopCategories($transactions, 10),
        ];

        // Calculate payment method percentages
        $totalRevenue = $analytics['total_revenue'];
        if ($totalRevenue > 0) {
            $analytics['payment_methods_breakdown'] = $analytics['payment_methods_breakdown']->map(function ($data) use ($totalRevenue) {
                $data['percentage'] = round(($data['total'] / $totalRevenue) * 100, 2);

                return $data;
            });
        }

        return response()->json([
            'data' => $analytics,
        ]);
    }

    /**
     * Get date range based on period.
     */
    private function getDateRange(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $now = now();

        return match ($period) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'yesterday' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $startDate ? Carbon::parse($startDate)->startOfDay() : $now->copy()->startOfMonth(),
                $endDate ? Carbon::parse($endDate)->endOfDay() : $now->copy()->endOfMonth(),
            ],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    /**
     * Get analytics date range based on period.
     */
    private function getAnalyticsDateRange(string $period): array
    {
        $now = now();

        return match ($period) {
            '7days' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
            '30days' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
            '90days' => [$now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()],
            '1year' => [$now->copy()->subYear()->startOfDay(), $now->copy()->endOfDay()],
            default => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
        };
    }

    /**
     * Get top products from transactions.
     */
    private function getTopProducts($transactions, int $limit = 5): array
    {
        $productSales = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $productId = $item->product_id;
                if (! isset($productSales[$productId])) {
                    $productSales[$productId] = [
                        'product' => $item->product,
                        'quantity_sold' => 0,
                        'total_revenue' => 0,
                    ];
                }
                $productSales[$productId]['quantity_sold'] += $item->quantity;
                $productSales[$productId]['total_revenue'] += $item->subtotal;
            }
        }

        // Sort by quantity sold
        uasort($productSales, function ($a, $b) {
            return $b['quantity_sold'] <=> $a['quantity_sold'];
        });

        return array_slice(array_values($productSales), 0, $limit);
    }

    /**
     * Get top categories from transactions.
     */
    private function getTopCategories($transactions, int $limit = 5): array
    {
        $categorySales = [];

        foreach ($transactions as $transaction) {
            foreach ($transaction->items as $item) {
                $categoryId = $item->product->category_id;
                $categoryName = $item->product->category->name;

                if (! isset($categorySales[$categoryId])) {
                    $categorySales[$categoryId] = [
                        'category_id' => $categoryId,
                        'category_name' => $categoryName,
                        'quantity_sold' => 0,
                        'total_revenue' => 0,
                    ];
                }
                $categorySales[$categoryId]['quantity_sold'] += $item->quantity;
                $categorySales[$categoryId]['total_revenue'] += $item->subtotal;
            }
        }

        // Sort by total revenue
        uasort($categorySales, function ($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        return array_slice(array_values($categorySales), 0, $limit);
    }

    /**
     * Get hourly sales data.
     */
    private function getHourlySales($transactions): array
    {
        $hourlySales = [];

        foreach ($transactions as $transaction) {
            $hour = $transaction->created_at->format('H');
            if (! isset($hourlySales[$hour])) {
                $hourlySales[$hour] = [
                    'hour' => $hour,
                    'transactions' => 0,
                    'revenue' => 0,
                ];
            }
            $hourlySales[$hour]['transactions']++;
            $hourlySales[$hour]['revenue'] += $transaction->total_amount;
        }

        // Fill missing hours with zero
        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT);
            if (! isset($hourlySales[$hour])) {
                $hourlySales[$hour] = [
                    'hour' => $hour,
                    'transactions' => 0,
                    'revenue' => 0,
                ];
            }
        }

        ksort($hourlySales);

        return array_values($hourlySales);
    }

    /**
     * Get sales trend data.
     */
    private function getSalesTrend($transactions, string $groupBy): array
    {
        $salesTrend = [];

        foreach ($transactions as $transaction) {
            $key = match ($groupBy) {
                'day' => $transaction->created_at->format('Y-m-d'),
                'week' => $transaction->created_at->format('Y-W'),
                'month' => $transaction->created_at->format('Y-m'),
                default => $transaction->created_at->format('Y-m-d'),
            };

            if (! isset($salesTrend[$key])) {
                $salesTrend[$key] = [
                    'period' => $key,
                    'transactions' => 0,
                    'revenue' => 0,
                    'income' => 0,
                ];
            }

            $salesTrend[$key]['transactions']++;
            $salesTrend[$key]['revenue'] += $transaction->total_amount;
            $salesTrend[$key]['income'] += $transaction->income;
        }

        ksort($salesTrend);

        return array_values($salesTrend);
    }
}
