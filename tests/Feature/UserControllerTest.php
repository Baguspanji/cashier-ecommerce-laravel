<?php

use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('UserController Index', function () {
    it('admin can view users index', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)
            ->get(route('users.index'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Users/Index')
                ->has('users', 4) // 3 created + 1 admin
                ->has('roles')
            );
    });

    it('manager cannot view users index', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        User::factory()->count(2)->create();

        $response = $this->actingAs($manager)
            ->get(route('users.index'));

        $response->assertForbidden();
    });

    it('cashier cannot view users index', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($cashier)
            ->get(route('users.index'));

        $response->assertForbidden();
    });

    it('guest cannot view users index', function () {
        $response = $this->get(route('users.index'));

        $response->assertRedirect('/login');
    });
});

describe('UserController Store', function () {
    it('admin can create a new user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Cashier->value,
        ];

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $userData);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User berhasil dibuat.');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => UserRole::Cashier->value,
        ]);

        $user = User::where('email', 'john@example.com')->first();
        expect(Hash::check('password123', $user->password))->toBeTrue();
    });

    it('manager cannot create a new user', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);

        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Cashier->value,
        ];

        $response = $this->actingAs($manager)
            ->post(route('users.store'), $userData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'jane@example.com',
        ]);
    });

    it('cashier cannot create a new user', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Cashier->value,
        ];

        $response = $this->actingAs($cashier)
            ->post(route('users.store'), $userData);

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'email' => 'john@example.com',
        ]);
    });

    it('validates required fields when creating user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $response = $this->actingAs($admin)
            ->post(route('users.store'), []);

        $response->assertSessionHasErrors(['name', 'email']);
    });

    it('validates email uniqueness when creating user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => UserRole::Cashier->value,
        ];

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    });

    it('validates password confirmation when creating user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different_password',
            'role' => UserRole::Cashier->value,
        ];

        $response = $this->actingAs($admin)
            ->post(route('users.store'), $userData);

        $response->assertSessionHasErrors(['password']);
    });
});

describe('UserController Update', function () {
    it('admin can update user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
            'role' => UserRole::Cashier->value,
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => UserRole::Manager->value,
        ];

        $response = $this->actingAs($admin)
            ->patch(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => UserRole::Manager->value,
        ]);
    });

    it('admin can update user password', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);
        $originalPassword = $user->password;

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->actingAs($admin)
            ->patch(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        expect($user->password)->not->toBe($originalPassword);
        expect(Hash::check('newpassword123', $user->password))->toBeTrue();
    });

    it('admin can update user without changing password', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);
        $originalPassword = $user->password;

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role->value,
        ];

        $response = $this->actingAs($admin)
            ->patch(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'));

        $user->refresh();
        expect($user->password)->toBe($originalPassword);
        expect($user->name)->toBe('Updated Name');
    });

    it('manager cannot update user', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role->value,
        ];

        $response = $this->actingAs($manager)
            ->patch(route('users.update', $user), $updateData);

        $response->assertForbidden();
    });

    it('cashier cannot update user', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role->value,
        ];

        $response = $this->actingAs($cashier)
            ->patch(route('users.update', $user), $updateData);

        $response->assertForbidden();
    });

    it('validates email uniqueness when updating user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user1 = User::factory()->create(['email' => 'user1@example.com', 'role' => UserRole::Cashier->value]);
        $user2 = User::factory()->create(['email' => 'user2@example.com', 'role' => UserRole::Cashier->value]);

        $updateData = [
            'name' => $user1->name,
            'email' => 'user2@example.com', // Trying to use user2's email
            'role' => $user1->role->value,
        ];

        $response = $this->actingAs($admin)
            ->patch(route('users.update', $user1), $updateData);

        $response->assertSessionHasErrors(['email']);
    });

    it('allows user to keep their own email when updating', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user = User::factory()->create(['email' => 'user@example.com', 'role' => UserRole::Cashier->value]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'user@example.com', // Same email
            'role' => $user->role->value,
        ];

        $response = $this->actingAs($admin)
            ->patch(route('users.update', $user), $updateData);

        $response->assertRedirect(route('users.index'))
            ->assertSessionHasNoErrors();
    });
});

describe('UserController Destroy', function () {
    it('admin can delete user', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $user));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'User berhasil dihapus.');

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    });

    it('manager cannot delete user', function () {
        $manager = User::factory()->create(['role' => UserRole::Manager->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($manager)
            ->delete(route('users.destroy', $user));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    });

    it('cashier cannot delete user', function () {
        $cashier = User::factory()->create(['role' => UserRole::Cashier->value]);
        $user = User::factory()->create(['role' => UserRole::Cashier->value]);

        $response = $this->actingAs($cashier)
            ->delete(route('users.destroy', $user));

        $response->assertForbidden();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
        ]);
    });

    it('user cannot delete themselves', function () {
        $admin = User::factory()->create(['role' => UserRole::Admin->value]);

        $response = $this->actingAs($admin)
            ->delete(route('users.destroy', $admin));

        $response->assertRedirect(route('users.index'))
            ->assertSessionHas('error', 'Anda tidak dapat menghapus akun sendiri.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    });
});

describe('UserController Authorization', function () {
    it('all methods require authentication', function () {
        $user = User::factory()->create();

        // Test all routes require authentication
        $this->get(route('users.index'))->assertRedirect('/login');
        $this->post(route('users.store'), [])->assertRedirect('/login');
        $this->patch(route('users.update', $user), [])->assertRedirect('/login');
        $this->delete(route('users.destroy', $user))->assertRedirect('/login');
    });
});
