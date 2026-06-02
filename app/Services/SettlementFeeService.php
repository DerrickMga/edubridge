<?php

namespace App\Services;

/**
 * Settlement fee structure for EduBridge teacher payouts.
 *
 * Fee rates are charged on the gross withdrawal amount (percentage).
 * Net amount = gross - fee.
 *
 * Based on VitalBot MerchantFeeService fee structure.
 */
class SettlementFeeService
{
    /**
     * Settlement fee rates by payment method (% of gross).
     */
    public static array $fees = [
        'omari'        => 1.5,   // O'mari wallet (ZW) — lowest, provider free
        'paystack'     => 3.5,   // Paystack SA bank EFT
        'bank_transfer'=> 3.5,   // ZW/UK bank transfer
        'ecocash'      => 3.5,   // EcoCash manual
        'innbucks'     => 3.5,   // InnBucks manual
        'paynow'       => 3.5,   // Paynow
        'cash_token'   => 3.5,   // Cash withdrawal token (collect at agent)
    ];

    /**
     * Human-readable labels for each method.
     */
    public static array $labels = [
        'omari'        => "O'mari Wallet (ZW)",
        'paystack'     => 'Paystack — SA Bank/Card',
        'bank_transfer'=> 'Bank Transfer',
        'ecocash'      => 'EcoCash',
        'innbucks'     => 'InnBucks',
        'paynow'       => 'Paynow',
        'cash_token'   => 'Cash Withdrawal Token',
    ];

    /**
     * Get the fee percentage for a given method (defaults to 3.5).
     */
    public static function feePercent(string $method): float
    {
        return self::$fees[$method] ?? 3.5;
    }

    /**
     * Calculate fee and net for a given gross amount and method.
     *
     * @return array{fee_pct: float, fee_usd: float, net_usd: float}
     */
    public static function calculate(float $grossAmount, string $method): array
    {
        $pct    = self::feePercent($method);
        $fee    = round($grossAmount * $pct / 100, 2);
        $net    = round($grossAmount - $fee, 2);

        return [
            'fee_pct' => $pct,
            'fee_usd' => $fee,
            'net_usd' => $net,
        ];
    }
}
