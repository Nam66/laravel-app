<?php

namespace App\Services;

use App\Contracts\PaymentGateway;

class StripePaymentGateway implements PaymentGateway
{
    public function charge(float $amount, string $currency): bool
    {
        // Gọi Stripe SDK ở đây
        // Ví dụ:
        // $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        // $stripe->charges->create([...]);

        return true;
    }
}