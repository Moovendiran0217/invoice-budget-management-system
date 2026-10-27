<?php

namespace App\Support;

class FinanceCategories
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Inventory',
            'Office',
            'Utilities',
            'Payroll',
            'Marketing',
            'Travel',
            'Software',
            'Logistics',
            'Maintenance',
            'Professional Services',
            'Other',
        ];
    }
}
