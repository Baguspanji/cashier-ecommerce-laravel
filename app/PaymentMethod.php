<?php

namespace App;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case DebitCard = 'debit_card';
    case CreditCard = 'credit_card';
    case BankTransfer = 'bank_transfer';
    case EWallet = 'e_wallet';
    case QRIS = 'qris';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::DebitCard => 'Kartu Debit',
            self::CreditCard => 'Kartu Kredit',
            self::BankTransfer => 'Transfer Bank',
            self::EWallet => 'E-Wallet',
            self::QRIS => 'QRIS',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Cash => '💵',
            self::DebitCard => '💳',
            self::CreditCard => '💳',
            self::BankTransfer => '🏦',
            self::EWallet => '📱',
            self::QRIS => '📱',
        };
    }

    public function requiresChange(): bool
    {
        return $this === self::Cash;
    }

    public function isElectronic(): bool
    {
        return in_array($this, [
            self::DebitCard,
            self::CreditCard,
            self::BankTransfer,
            self::EWallet,
            self::QRIS,
        ]);
    }

    public static function getAvailable(): array
    {
        return collect(self::cases())->map(function ($method) {
            return [
                'value' => $method->value,
                'label' => $method->label(),
                'icon' => $method->icon(),
                'requires_change' => $method->requiresChange(),
                'is_electronic' => $method->isElectronic(),
            ];
        })->toArray();
    }
}
