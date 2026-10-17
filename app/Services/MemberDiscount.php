<?php

namespace App\Services;

use App\Contracts\DiscountStrategy;

class MemberDiscount implements DiscountStrategy
{
    public function apply(float $amount): float
    {
        return $amount * 0.9;
    }
}
