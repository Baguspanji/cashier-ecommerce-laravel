<?php

use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can be created with valid data', function () {
    $user = User::create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('John Doe')
        ->and($user->email)->toBe('john@example.com');
});

test('user has transactions relationship', function () {
    $user = User::factory()->create();

    // Create transactions with unique numbers
    $transactions = [];
    for ($i = 0; $i < 3; $i++) {
        $transactions[] = Transaction::factory()->create([
            'user_id' => $user->id,
            'transaction_number' => 'TRX' . now()->format('Ymd') . str_pad($i + 1000, 4, '0', STR_PAD_LEFT),
        ]);
    }

    expect($user->transactions)->toHaveCount(3)
        ->and($user->transactions->first())->toBeInstanceOf(Transaction::class);
});

test('user has stock movements relationship', function () {
    $user = User::factory()->create();
    StockMovement::factory(2)->create(['user_id' => $user->id]);

    expect($user->stockMovements)->toHaveCount(2)
        ->and($user->stockMovements->first())->toBeInstanceOf(StockMovement::class);
});

test('user factory creates valid user', function () {
    $user = User::factory()->create();

    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->name)->toBeString()
        ->and($user->email)->toBeString()
        ->and($user->password)->toBeString()
        ->and($user->created_at)->not->toBeNull();
});

test('user password is hashed', function () {
    $user = User::factory()->create();

    expect($user->password)
        ->not->toBe('password')
        ->and(strlen($user->password))->toBeGreaterThan(10);
});

test('user email is unique', function () {
    User::factory()->create(['email' => 'test@example.com']);

    $this->expectException(\Illuminate\Database\QueryException::class);

    User::factory()->create(['email' => 'test@example.com']);
});

test('user returns correct relationship instances', function () {
    $user = User::factory()->create();

    expect($user->transactions())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class)
        ->and($user->stockMovements())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('user hidden attributes are not visible', function () {
    $user = User::factory()->create();

    expect($user->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

test('user email_verified_at is cast to datetime', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

test('user uses notifiable trait', function () {
    $user = User::factory()->create();

    expect(method_exists($user, 'notify'))->toBeTrue();
});

test('user uses has factory trait', function () {
    expect(method_exists(User::class, 'factory'))->toBeTrue();
});
