<?php

namespace Tests\Unit;

use App\Services\RepaymentScheduleService;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class RepaymentScheduleServiceTest extends TestCase
{
    public function test_interest_summary_is_calculated_correctly(): void
    {
        $service = new RepaymentScheduleService;
        $summary = $service->summary(1200000, 12, 10);

        $this->assertSame(120000.0, $summary['total_interest']);
        $this->assertSame(1320000.0, $summary['total_payable']);
        $this->assertSame(110000.0, $summary['monthly_installment']);
    }

    public function test_repayment_schedule_has_correct_dates_and_amounts(): void
    {
        $service = new RepaymentScheduleService;
        $approvedAt = Carbon::create(2026, 1, 31);
        $schedule = $service->calculate(1200000, 3, 10, $approvedAt);

        $this->assertCount(3, $schedule);
        $this->assertSame(1, $schedule[0]['installment_number']);
        $this->assertSame('2026-02-28', $schedule[0]['due_date']);
        $this->assertSame('2026-03-31', $schedule[1]['due_date']);
        $this->assertSame('2026-04-30', $schedule[2]['due_date']);
        $this->assertSame('pending', $schedule[0]['status']);
    }

    public function test_last_installment_fixes_rounding_difference(): void
    {
        $service = new RepaymentScheduleService;
        $approvedAt = Carbon::create(2026, 1, 1);
        $schedule = $service->calculate(100000, 3, 10, $approvedAt);

        $total = array_sum(array_column($schedule, 'amount'));

        $this->assertEqualsWithDelta(102500, $total, 0.01);
        $this->assertSame(34166.66, $schedule[2]['amount']);
    }
}
