<?php

namespace Tests\Unit;

use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
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

    public function test_installment_progress_allocates_paid_amount_in_order(): void
    {
        $debt = new Debt([
            'original_amount_minor' => 1000000,
            'installment_count' => 3,
        ]);
        $debt->setRelation('payments', new Collection([
            new Payment(['amount_minor' => 400000]),
        ]));

        $progress = $debt->installmentProgress();

        $this->assertSame('paid', $progress[0]['status']);
        $this->assertSame(266666, $progress[1]['outstanding_minor']);
        $this->assertSame('open', $progress[2]['status']);
    }
}
