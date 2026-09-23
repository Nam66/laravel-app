<?php

namespace App\Contracts;

interface PaymentGateway
{
    public function charge(float $amount, string $currency): bool;
}