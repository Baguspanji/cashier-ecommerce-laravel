<?php

namespace App\Providers;

use App\Models\User;
use App\UserRole;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->defineGates();
    }

    /**
     * Define authorization gates.
     */
    protected function defineGates(): void
    {
        // Admin can do everything
        Gate::before(function (User $user, string $ability) {
            if ($user->role === UserRole::Admin) {
                return true;
            }
        });

        // Product management
        Gate::define('manage_products', function (User $user) {
            return $user->hasPermission('manage_products');
        });

        Gate::define('view_products', function (User $user) {
            return $user->hasPermission('view_products') || $user->hasPermission('manage_products');
        });

        // Category management
        Gate::define('manage_categories', function (User $user) {
            return $user->hasPermission('manage_categories');
        });

        Gate::define('view_categories', function (User $user) {
            return $user->hasPermission('view_categories') || $user->hasPermission('manage_categories');
        });

        // Transaction management
        Gate::define('manage_transactions', function (User $user) {
            return $user->hasPermission('manage_transactions');
        });

        Gate::define('create_transactions', function (User $user) {
            return $user->hasPermission('create_transactions') || $user->hasPermission('manage_transactions');
        });

        // Stock management
        Gate::define('manage_stock', function (User $user) {
            return $user->hasPermission('manage_stock');
        });

        // Reports
        Gate::define('view_reports', function (User $user) {
            return $user->hasPermission('view_reports');
        });

        Gate::define('export_reports', function (User $user) {
            return $user->hasPermission('export_reports');
        });

        // User management
        Gate::define('manage_users', function (User $user) {
            return $user->hasPermission('manage_users');
        });

        // System settings
        Gate::define('system_settings', function (User $user) {
            return $user->hasPermission('system_settings');
        });
    }
}
