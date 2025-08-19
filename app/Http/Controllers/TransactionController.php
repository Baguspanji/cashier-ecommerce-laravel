<?php

namespace App\Http\Controllers;

use App\Data\CreateTransactionData;
use App\Data\ProductData;
use App\Data\TransactionData;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TransactionController extends Controller
{
    /**
     * Display a listing of transactions.
     */
    public function index()
    {
        $transactions = Transaction::with(['user', 'items.product'])
            ->when(request('search'), function ($query, $search) {
                $query->where('transaction_number', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('date_from'), function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when(request('date_to'), function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Transactions/Index', [
            'transactions' => $this->mapPagination($transactions, fn ($items) => TransactionData::collect($items)),
            'filters' => request()->only(['search', 'status', 'date_from', 'date_to']),
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
