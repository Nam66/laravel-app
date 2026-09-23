<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class MomoPaymentGateway implements PaymentGateway
{
    public function charge(float $amount, string $currency): bool
    {
        // Gọi Momo API ở đây
        // return response từ API
        return true;
    }
}