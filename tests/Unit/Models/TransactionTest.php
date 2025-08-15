<?php

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('transaction can be created with valid data', function () {
    $user = User::factory()->create();

    $transaction = Transaction::create([
        'transaction_number' => 'TRX202508150001',
        'user_id' => $user->id,
        'total_amount' => 50000,
        'payment_method' => 'cash',
        'payment_amount' => 50000,
        'change_amount' => 0,
        'status' => 'completed',
    ]);

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->and($transaction->transaction_number)->toBe('TRX202508150001')
        ->and($transaction->total_amount)->toBe('50000.00')
        ->and($transaction->payment_method)->toBe('cash')
        ->and($transaction->status)->toBe('completed');
});

test('transaction belongs to user', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->user)
        ->toBeInstanceOf(User::class)
        ->and($transaction->user_id)->toBe($transaction->user->id);
});

test('transaction has items relationship', function () {
    $transaction = Transaction::factory()->create();
    TransactionItem::factory(3)->create(['transaction_id' => $transaction->id]);

    expect($transaction->items)->toHaveCount(3)
        ->and($transaction->items->first())->toBeInstanceOf(TransactionItem::class);
});

test('transaction can generate unique transaction number', function () {
    $transactionNumber = Transaction::generateTransactionNumber();

    expect($transactionNumber)
        ->toBeString()
        ->toStartWith('TRX')
        ->toHaveLength(15); // TRX + 8 digit date + 4 digit counter
});

test('transaction generates sequential numbers for same day', function () {
    // Create a transaction for today
    $transaction1 = Transaction::factory()->create([
        'transaction_number' => Transaction::generateTransactionNumber(),
    ]);

    $transaction2 = Transaction::factory()->create([
        'transaction_number' => Transaction::generateTransactionNumber(),
    ]);

    expect($transaction1->transaction_number)
        ->not->toBe($transaction2->transaction_number);
});

test('transaction factory creates valid transaction', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction)
        ->toBeInstanceOf(Transaction::class)
        ->and($transaction->transaction_number)->toBeString()
        ->and($transaction->total_amount)->toBeNumeric()
        ->and($transaction->payment_amount)->toBeNumeric()
        ->and($transaction->change_amount)->toBeNumeric();
});

test('transaction amounts are cast to decimal', function () {
    $transaction = Transaction::factory()->create([
        'total_amount' => 15000,
        'payment_amount' => 15000,
        'change_amount' => 0,
    ]);

    expect($transaction->total_amount)->toBe('15000.00')
        ->and($transaction->payment_amount)->toBe('15000.00')
        ->and($transaction->change_amount)->toBe('0.00');
});

test('transaction returns correct relationship instances', function () {
    $transaction = Transaction::factory()->create();

    expect($transaction->user())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class)
        ->and($transaction->items())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('transaction number format is correct', function () {
    $transactionNumber = Transaction::generateTransactionNumber();
    $today = now()->format('Ymd');

    expect($transactionNumber)
        ->toStartWith('TRX' . $today)
        ->toMatch('/^TRX\d{8}\d{4}$/');
});

test('transaction number increments properly', function () {
    // Clear any existing transactions for today
    Transaction::whereDate('created_at', today())->delete();

    $firstNumber = Transaction::generateTransactionNumber();
    Transaction::factory()->create(['transaction_number' => $firstNumber]);

    $secondNumber = Transaction::generateTransactionNumber();

    $firstCounter = substr($firstNumber, -4);
    $secondCounter = substr($secondNumber, -4);

    expect((int) $secondCounter)->toBe((int) $firstCounter + 1);
});
