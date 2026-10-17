<?php

namespace App\Services;

use App\Contracts\DiscountStrategy;

class VipDiscount implements DiscountStrategy
{
    public function apply(float $amount): float
    {
        return $amount * 0.75;
    }
}
