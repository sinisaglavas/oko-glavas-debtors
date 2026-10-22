<?php

namespace Tests\Unit;

use App\Models\Debt;
use PHPUnit\Framework\TestCase;

class DebtCalculationTest extends TestCase
{
    public function test_installment_schedule_puts_rounding_remainder_in_last_installment(): void
    {
        $debt = new Debt([
            'original_amount_minor' => 1000000,
            'installment_count' => 3,
        ]);

        $this->assertSame([333333, 333333, 333334], $debt->installmentScheduleMinor());
    }
}
