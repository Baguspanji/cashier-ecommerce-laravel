<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case Cashier = 'cashier';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Cashier => 'Kasir',
            self::Manager => 'Manager',
        };
    }

    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [
                'manage_products',
                'manage_categories',
                'manage_transactions',
                'manage_stock',
                'view_reports',
                'export_reports',
                'manage_users',
                'system_settings',
            ],
            self::Manager => [
                'manage_products',
                'manage_categories',
                'manage_transactions',
                'manage_stock',
                'view_reports',
                'export_reports',
            ],
            self::Cashier => [
                'create_transactions',
                'view_products',
                'view_categories',
            ],
        };
    }
}
