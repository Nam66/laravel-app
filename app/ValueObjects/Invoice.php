<?php

namespace App\ValueObjects;

class Invoice
{
    public function __construct(
        public readonly string $customer,
        public readonly float $amount,
        public readonly string $currency,
        public readonly float $tax,
        public readonly string $notes,
        public readonly string $status,
        public readonly string $createdAt,
    ) {
    }
}
