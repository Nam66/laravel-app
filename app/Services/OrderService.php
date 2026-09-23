<?php

namespace App\Services;

use App\Contracts\PaymentGateway;
use App\Models\Order;

class OrderService
{
    public function __construct(
        private readonly PaymentGateway $paymentGateway
    ) {
    }

    public function create(array $data): Order
    {
        $amount = (float) $data['amount'];
        $currency = $data['currency'] ?? 'VND';

        $paid = $this->paymentGateway->charge($amount, $currency);

        if (! $paid) {
            throw new \RuntimeException('Payment failed.');
        }

        return Order::create([
            'item_name' => $data['item_name'],
            'quantity' => $data['quantity'] ?? 1,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'paid',
        ]);
    }
}