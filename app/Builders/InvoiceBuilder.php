<?php

namespace App\Builders;

use App\ValueObjects\Invoice;

class InvoiceBuilder
{
    private string $customer = '';

    private float $amount = 0;

    private string $currency = 'VND';

    private float $tax = 0;

    private string $notes = '';

    private string $status = 'draft';

    private string $createdAt;

    public function __construct()
    {
        $this->createdAt = now()->toDateTimeString();
    }

    public function customer(string $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function amount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function currency(string $currency): self
    {
        $this->currency = strtoupper($currency);

        return $this;
    }

    public function tax(float $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function notes(string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }

    public function status(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function createdAt(string $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function build(): Invoice
    {
        return new Invoice(
            customer: $this->customer,
            amount: $this->amount,
            currency: $this->currency,
            tax: $this->tax,
            notes: $this->notes,
            status: $this->status,
            createdAt: $this->createdAt,
        );
    }
}
