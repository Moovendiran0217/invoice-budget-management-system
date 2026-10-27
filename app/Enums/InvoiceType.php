<?php

namespace App\Enums;

enum InvoiceType: string
{
    case Purchase = 'purchase';
    case Sales = 'sales';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Sales => 'Sales',
        };
    }

    public function numberPrefix(): string
    {
        return match ($this) {
            self::Purchase => 'PINV',
            self::Sales => 'SINV',
        };
    }
}
