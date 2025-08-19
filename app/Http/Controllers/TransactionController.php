<?php

namespace App\Http\Controllers;

use App\Data\CreateTransactionData;
use App\Data\ProductData;
use App\Data\TransactionData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions (Reports Page).
     */
    public function index(Request $request)
    {
        Gate::authorize('view_reports');

        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'period' => ['nullable', 'in:today,yesterday,this_week,last_week,this_month,last_month,this_year,custom'],
            'payment_method' => ['nullable', 'string'],
            'user_id' => ['nullable', 'integer'],
        ]);

        $period = $request->get('period', 'this_month');
        $dateRange = $this->getDateRange($period, $request->start_date, $request->end_date);

        $transactions = Transaction::with(['user', 'items.product'])
            ->whereBetween('created_at', $dateRange)
            ->when($request->payment_method, function ($query, $paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('transaction_number', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Summary statistics
        $allTransactions = Transaction::whereBetween('created_at', $dateRange)
            ->when($request->payment_method, function ($query, $paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->with('items')
            ->get();

        $summary = [
            'total_transactions' => $allTransactions->count(),
            'total_revenue' => $allTransactions->sum('total_amount'),
            'total_items_sold' => $allTransactions->sum(fn ($t) => $t->items->sum('quantity')),
            'average_transaction' => $allTransactions->count() > 0 ? $allTransactions->avg('total_amount') : 0,
        ];

        // Payment method breakdown
        $paymentMethods = $allTransactions->groupBy('payment_method')
            ->map(function ($group, $method) {
                return [
                    'method' => $method,
                    'count' => $group->count(),
                    'total' => $group->sum('total_amount'),
                    'percentage' => 0, // Will be calculated in frontend
                ];
            })->values();

        // Top products
        $topProducts = $allTransactions->flatMap->items
            ->groupBy('product_id')
            ->map(function ($items) {
                $product = $items->first()->product;

                return [
                    'product_name' => $product ? $product->name : 'Produk Terhapus',
                    'quantity_sold' => $items->sum('quantity'),
                    'revenue' => $items->sum('subtotal'),
                ];
            })
            ->sortByDesc('quantity_sold')
            ->take(10)
            ->values();

        // Daily sales chart data
        $dailySales = $allTransactions
            ->groupBy(fn ($t) => $t->created_at->format('Y-m-d'))
            ->map(function ($dayTransactions, $date) {
                return [
                    'date' => $date,
                    'total_amount' => $dayTransactions->sum('total_amount'),
                    'transaction_count' => $dayTransactions->count(),
                ];
            })
            ->sortBy('date')
            ->values();

        // Get users for filter
        $users = \App\Models\User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $this->mapPagination($transactions, fn ($items) => TransactionData::collect($items)),
            'summary' => $summary,
            'payment_methods' => $paymentMethods,
            'top_products' => $topProducts,
            'daily_sales' => $dailySales,
            'users' => $users,
            'filters' => [
                'period' => $period,
                'start_date' => $dateRange[0]->format('Y-m-d'),
                'end_date' => $dateRange[1]->format('Y-m-d'),
                'payment_method' => $request->payment_method,
                'user_id' => $request->user_id,
                'search' => $request->search,
            ],
            'available_payment_methods' => [
                'cash' => 'Tunai',
                'debit_card' => 'Kartu Debit',
                'credit_card' => 'Kartu Kredit',
                'bank_transfer' => 'Transfer Bank',
                'e_wallet' => 'E-Wallet',
                'qris' => 'QRIS',
            ],
        ]);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['user', 'items.product.category']);

        return Inertia::render('Transactions/Show', [
            'transaction' => TransactionData::from($transaction),
        ]);
    }

    /**
     * Show the POS (Point of Sale) interface.
     */
    public function pos()
    {
        $products = Product::with('category')
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->orderBy('name')
            ->get();

        return Inertia::render('Transactions/POS', [
            'products' => ProductData::collect($products),
        ]);
    }

    /**
     * Process a new transaction.
     */
    public function store(CreateTransactionData $data)
    {
        return DB::transaction(function () use ($data) {
            // Generate transaction number
            $transactionNumber = Transaction::generateTransactionNumber();

            // Calculate totals
            $totalAmount = 0;
            $items = [];

            foreach ($data->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Check stock availability
                if ($product->current_stock < $item['quantity']) {
                    throw new \Exception("Stok produk {$product->name} tidak mencukupi.");
                }

                $subtotal = $product->price * $item['quantity'];
                $totalAmount += $subtotal;

                $items[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            }

            $changeAmount = $data->payment_amount - $totalAmount;

            if ($changeAmount < 0) {
                throw new \Exception('Jumlah pembayaran tidak mencukupi.');
            }

            // Create transaction
            $transaction = Transaction::create([
                'transaction_number' => $transactionNumber,
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
                'payment_method' => $data->payment_method,
                'payment_amount' => $data->payment_amount,
                'change_amount' => $changeAmount,
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
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);

                // Create stock movement and update product stock
                StockMovement::createMovement(
                    $item['product'],
                    'out',
                    $item['quantity'],
                    referenceId: $transaction->id,
                    referenceType: 'transaction',
                    notes: "Penjualan - {$transactionNumber}"
                );
            }

            return redirect()->route('transactions.show', $transaction)
                ->with('success', 'Transaksi berhasil diproses.');
        });
    }

    /**
     * Show daily sales report.
     */
    public function dailyReport()
    {
        $date = request('date', today()->format('Y-m-d'));

        $transactions = Transaction::with(['items.product.category'])
            ->whereDate('created_at', $date)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_transactions' => $transactions->count(),
            'total_amount' => $transactions->sum('total_amount'),
            'total_items_sold' => $transactions->sum(function ($transaction) {
                return $transaction->items->sum('quantity');
            }),
            'payment_methods' => $transactions->groupBy('payment_method')
                ->map(function ($group, $method) {
                    return [
                        'method' => $method,
                        'count' => $group->count(),
                        'total' => $group->sum('total_amount'),
                    ];
                })->values(),
        ];

        return Inertia::render('Transactions/DailyReport', [
            'transactions' => TransactionData::collect($transactions),
            'summary' => $summary,
            'date' => $date,
        ]);
    }

    /**
     * Export transaction report
     */
    public function exportReport(Request $request)
    {
        Gate::authorize('export_reports');

        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'format' => ['required', 'in:pdf,excel'],
        ]);

        $dateRange = [
            Carbon::parse($request->start_date)->startOfDay(),
            Carbon::parse($request->end_date)->endOfDay(),
        ];

        $transactions = Transaction::whereBetween('created_at', $dateRange)
            ->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->get('format') === 'pdf') {
            return $this->exportPdf($transactions, $dateRange);
        }

        return $this->exportExcel($transactions, $dateRange);
    }

    /**
     * Get date range based on period
     */
    private function getDateRange(string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        $now = Carbon::now();

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
     * Export data to PDF
     */
    private function exportPdf($transactions, $dateRange)
    {
        return response()->streamDownload(function () use ($transactions, $dateRange) {
            echo "LAPORAN TRANSAKSI\n";
            echo "================\n";
            echo "Periode: {$dateRange[0]->format('d/m/Y')} - {$dateRange[1]->format('d/m/Y')}\n";
            echo 'Tanggal Cetak: '.now()->format('d/m/Y H:i')."\n\n";

            $totalAmount = 0;
            foreach ($transactions as $transaction) {
                echo "No. Transaksi: {$transaction->transaction_number}\n";
                echo "Tanggal: {$transaction->created_at->format('d/m/Y H:i')}\n";
                echo "Kasir: {$transaction->user->name}\n";
                echo "Metode Bayar: {$transaction->payment_method}\n";
                echo 'Total: Rp '.number_format($transaction->total_amount, 0, ',', '.')."\n";
                echo "------------------------\n";
                $totalAmount += $transaction->total_amount;
            }

            echo "\nRINGKASAN:\n";
            echo 'Total Transaksi: '.$transactions->count()."\n";
            echo 'Total Pendapatan: Rp '.number_format($totalAmount, 0, ',', '.')."\n";
        }, 'laporan-transaksi-'.now()->format('Y-m-d').'.txt');
    }

    /**
     * Export data to Excel (CSV format)
     */
    private function exportExcel($transactions, $dateRange)
    {
        return response()->streamDownload(function () use ($transactions) {
            $output = fopen('php://output', 'w');

            // Headers
            fputcsv($output, [
                'No. Transaksi',
                'Tanggal',
                'Kasir',
                'Total',
                'Metode Pembayaran',
                'Status',
            ]);

            // Data
            foreach ($transactions as $transaction) {
                fputcsv($output, [
                    $transaction->transaction_number,
                    $transaction->created_at->format('d/m/Y H:i'),
                    $transaction->user->name,
                    $transaction->total_amount,
                    $transaction->payment_method,
                    $transaction->status,
                ]);
            }

            fclose($output);
        }, 'laporan-transaksi-'.now()->format('Y-m-d').'.csv');
    }

    /**
     * Cancel a transaction (if allowed).
     */
    public function cancel(Transaction $transaction)
    {
        if ($transaction->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Transaksi tidak dapat dibatalkan.');
        }

        return DB::transaction(function () use ($transaction) {
            // Restore stock for each item
            foreach ($transaction->items as $item) {
                StockMovement::createMovement(
                    $item->product,
                    'in',
                    $item->quantity,
                    referenceId: $transaction->id,
                    referenceType: 'cancellation',
                    notes: "Pembatalan transaksi - {$transaction->transaction_number}"
                );
            }

            // Update transaction status
            $transaction->update(['status' => 'cancelled']);

            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil dibatalkan dan stok telah dikembalikan.');
        });
    }
}
