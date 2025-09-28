<?php

use App\Models\User;
use App\UserRole;
use Laravel\Sanctum\Sanctum;

test('can login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => UserRole::Admin,
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'Test Device',
    ]);

    $response->assertSuccessful()
        ->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'role',
            ],
            'token',
        ])
        ->assertJsonPath('user.email', 'test@example.com')
        ->assertJsonPath('user.role', 'admin');

    expect($response->json('token'))->not->toBeNull();
});

test('cannot login with invalid credentials', function () {
    User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('cannot login with non-existent email', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('validates required fields for login', function () {
    $response = $this->postJson('/api/login', []);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

test('validates email format for login', function () {
    $response = $this->postJson('/api/login', [
        'email' => 'invalid-email',
        'password' => 'password',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('can get authenticated user information', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertSuccessful()
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
            ],
        ])
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonPath('user.email', $user->email);
});

test('cannot get user information without authentication', function () {
    $response = $this->getJson('/api/user');

    $response->assertUnauthorized();
});

test('can logout and revoke current token', function () {
    $user = User::factory()->create();
    $token = $user->createToken('Test Token');

    $response = $this->withHeader('Authorization', 'Bearer ' . $token->plainTextToken)
        ->postJson('/api/logout');

    $response->assertSuccessful()
        ->assertJsonStructure(['message']);

    // Verify token is revoked by checking database
    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('can revoke all tokens', function () {
    $user = User::factory()->create();
    $token1 = $user->createToken('Token 1');
    $token2 = $user->createToken('Token 2');

    expect($user->tokens)->toHaveCount(2);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token1->plainTextToken)
        ->postJson('/api/revoke-all-tokens');

    $response->assertSuccessful()
        ->assertJsonStructure(['message']);

    // Verify all tokens are revoked
    expect($user->fresh()->tokens)->toHaveCount(0);
});

test('cannot logout without authentication', function () {
    $response = $this->postJson('/api/logout');

    $response->assertUnauthorized();
});

test('cannot revoke tokens without authentication', function () {
    $response = $this->postJson('/api/revoke-all-tokens');

    $response->assertUnauthorized();
});

test('login creates token with device name', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'test@example.com',
        'password' => 'password',
        'device_name' => 'iPhone 15',
    ]);

    $response->assertSuccessful();

    expect($user->fresh()->tokens)->toHaveCount(1);
    expect($user->fresh()->tokens->first()->name)->toBe('iPhone 15');
});

test('login creates token with user agent when device name not provided', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
    ]);

    $response = $this->withHeader('User-Agent', 'TestBrowser/1.0')
        ->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

    $response->assertSuccessful();

    expect($user->fresh()->tokens)->toHaveCount(1);
    expect($user->fresh()->tokens->first()->name)->toBe('TestBrowser/1.0');
});
