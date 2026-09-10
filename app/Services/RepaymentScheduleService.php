<?php

namespace App\Services;

use App\Models\LoanApplication;
use Carbon\Carbon;

class RepaymentScheduleService
{
    public function summary(float $loanAmount, int $termMonths, float $annualInterestRate): array
    {
        $interestRate = $annualInterestRate / 100;
        $termInYears = $termMonths / 12;
        $totalInterest = $loanAmount * $interestRate * $termInYears;
        $totalInterest = round($totalInterest, 2);
        $totalPayable = round($loanAmount + $totalInterest, 2);
        $monthlyInstallment = round($totalPayable / $termMonths, 2);

        return [
            'total_interest' => $totalInterest,
            'total_payable' => $totalPayable,
            'monthly_installment' => $monthlyInstallment,
        ];
    }

    public function createFor(LoanApplication $loan, Carbon $approvedAt): void
    {
        $amount = (float) $loan->amount;
        $interestRate = (float) $loan->interest_rate;
        $schedule = $this->calculate($amount, $loan->term_months, $interestRate, $approvedAt);

        foreach ($schedule as $installment) {
            $loan->repayments()->create($installment);
        }
    }

    public function calculate(
        float $loanAmount,
        int $termMonths,
        float $annualInterestRate,
        Carbon $approvedAt
    ): array {
        $summary = $this->summary($loanAmount, $termMonths, $annualInterestRate);
        $regularAmount = $summary['monthly_installment'];
        $allocatedAmount = 0;
        $schedule = [];

        for ($installment = 1; $installment <= $termMonths; $installment++) {
            $amount = $regularAmount;

            if ($installment === $termMonths) {
                $amount = round($summary['total_payable'] - $allocatedAmount, 2);
            }

            $dueDate = $approvedAt->copy();
            $dueDate->addMonthsNoOverflow($installment);

            $schedule[] = [
                'installment_number' => $installment,
                'due_date' => $dueDate->toDateString(),
                'amount' => $amount,
                'status' => 'pending',
            ];

            $allocatedAmount = round($allocatedAmount + $amount, 2);
        }

        return $schedule;
    }
}
