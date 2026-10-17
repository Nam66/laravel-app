<?php

namespace App\Services;

use App\Contracts\DiscountStrategy;

class GuestDiscount implements DiscountStrategy
{
    public function apply(float $amount): float
    {
        return $amount;
    }
}
