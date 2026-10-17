<?php

namespace App\Services;

use App\Contracts\DiscountStrategy;
use InvalidArgumentException;

class OrderDiscountService
{
    /**
     * @return DiscountStrategy
     */
    public function for(string $customerType): DiscountStrategy
    {
        return match (strtolower($customerType)) {
            'guest' => new GuestDiscount(),
            'member' => new MemberDiscount(),
            'vip' => new VipDiscount(),
            default => throw new InvalidArgumentException('Unsupported customer type: '.$customerType),
        };
    }

    public function calculate(string $customerType, float $amount): float
    {
        $strategy = $this->for($customerType);

        return round($strategy->apply($amount), 2);
    }
}
