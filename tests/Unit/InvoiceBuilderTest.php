<?php

namespace Tests\Unit;

use App\Builders\InvoiceBuilder;
use App\ValueObjects\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceBuilderTest extends TestCase
{
    public function test_it_builds_invoice_with_fluent_methods(): void
    {
        $invoice = (new InvoiceBuilder())
            ->customer('Alice Nguyen')
            ->amount(250000)
            ->currency('usd')
            ->tax(25000)
            ->notes('Monthly subscription')
            ->status('paid')
            ->createdAt('2026-09-23 10:00:00')
            ->build();

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertSame('Alice Nguyen', $invoice->customer);
        $this->assertSame(250000.0, $invoice->amount);
        $this->assertSame('USD', $invoice->currency);
        $this->assertSame(25000.0, $invoice->tax);
        $this->assertSame('Monthly subscription', $invoice->notes);
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('2026-09-23 10:00:00', $invoice->createdAt);
    }
}
