<?php

namespace App\Contracts;

interface DiscountStrategy
{
    public function apply(float $amount): float;
}
