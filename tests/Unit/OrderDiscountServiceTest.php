<?php

namespace Tests\Unit;

use App\Services\OrderDiscountService;
use InvalidArgumentException;
use Tests\TestCase;

class OrderDiscountServiceTest extends TestCase
{
    public function test_it_applies_guest_discount(): void
    {
        $service = new OrderDiscountService();

        $this->assertSame(100.0, $service->calculate('guest', 100.0));
    }

    public function test_it_applies_member_discount(): void
    {
        $service = new OrderDiscountService();

        $this->assertSame(90.0, $service->calculate('member', 100.0));
    }

    public function test_it_applies_vip_discount(): void
    {
        $service = new OrderDiscountService();

        $this->assertSame(75.0, $service->calculate('vip', 100.0));
    }

    public function test_it_throws_for_unknown_customer_type(): void
    {
        $service = new OrderDiscountService();

        $this->expectException(InvalidArgumentException::class);

        $service->calculate('staff', 100.0);
    }
}
