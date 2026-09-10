<?php

namespace Database\Seeders;

use App\Models\LoanApplication;
use App\Models\User;
use App\Services\RepaymentScheduleService;
use Illuminate\Database\Seeder;

class LoanApplicationSeeder extends Seeder
{
    public function run(RepaymentScheduleService $repaymentScheduleService): void
    {
        $customerUser = User::where('email', 'customer@example.com')->firstOrFail();
        $officer = User::where('email', 'officer@example.com')->firstOrFail();
        $customer = $customerUser->customer;

        $loan = LoanApplication::where('customer_id', $customer->id)
            ->where('purpose', 'Home improvement')
            ->first();

        if (! $loan) {
            $loan = LoanApplication::create([
                'customer_id' => $customer->id,
                'assigned_reviewer_id' => $officer->id,
                'amount' => 1200000,
                'term_months' => 12,
                'interest_rate' => config('loan.interest_rate'),
                'supporting_notes' => 'Renovation materials and labour costs',
                'decision_notes' => 'Sample approved application',
                'purpose' => 'Home improvement',
                'status' => 'approved',
                'application_date' => today()->subDays(7),
                'approved_at' => now()->subDays(5),
            ]);

            $repaymentScheduleService->createFor($loan, $loan->approved_at);
        }
    }
}
