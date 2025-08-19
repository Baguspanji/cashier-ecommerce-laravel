<?php

namespace App\Http\Controllers;

use App\Data\UserData;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('manage_users');

        $users = User::select(['id', 'name', 'email', 'role', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Users/Index', [
            'users' => UserData::collect($users),
            'roles' => collect(UserRole::cases())->map(function ($role) {
                return [
                    'value' => $role->value,
                    'label' => $role->label(),
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserData $data)
    {
        Gate::authorize('manage_users');

        User::create([
            'name' => $data->name,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'role' => $data->role,
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        Gate::authorize('manage_users');

        // Add user ID to request for validation context
        $requestData = $request->all();
        $requestData['id'] = $user->id;

        $data = UserData::validateAndCreate($requestData);

        $updateData = [
            'name' => $data->name,
            'email' => $data->email,
            'role' => $data->role,
        ];

        if (! empty($data->password)) {
            $updateData['password'] = Hash::make($data->password);
        }

        $user->update($updateData);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        Gate::authorize('manage_users');

        // Prevent deleting yourself
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
