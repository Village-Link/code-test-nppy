<?php

namespace App\Services;

use App\Jobs\CreateLoanNotification;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanRepaymentService
{
    public function submit(
        LoanApplication $loan,
        LoanRepayment $repayment,
        Customer $customer,
        float $amount,
        UploadedFile $screenshot
    ): void {
        DB::transaction(function () use ($loan, $repayment, $customer, $amount, $screenshot) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);
            $repayment = LoanRepayment::lockForUpdate()->findOrFail($repayment->id);

            if ($loan->customer_id !== $customer->id) {
                abort(403);
            }

            if ($repayment->loan_application_id !== $loan->id) {
                abort(404);
            }

            if ($loan->status !== 'disbursed') {
                throw ValidationException::withMessages([
                    'repayment' => 'Payments can only be submitted for disbursed loans.',
                ]);
            }

            if ($repayment->status !== 'pending') {
                throw ValidationException::withMessages([
                    'repayment' => 'This installment has already been submitted.',
                ]);
            }

            if (round($amount, 2) !== round((float) $repayment->amount, 2)) {
                throw ValidationException::withMessages([
                    'amount' => 'The payment amount must match the installment amount.',
                ]);
            }

            $path = $screenshot->store('repayment-proofs', 'local');

            $repayment->update([
                'paid_amount' => $amount,
                'payment_screenshot' => $path,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);
        });

        if ($loan->assigned_reviewer_id) {
            $this->sendNotification(
                $loan->assigned_reviewer_id,
                "Payment for installment #{$repayment->installment_number} of loan #{$loan->id} was submitted.",
                route('loan-officer.loans.show', $loan)
            );
        } else {
            $adminIds = User::role('admin')->pluck('id');

            foreach ($adminIds as $adminId) {
                $this->sendNotification(
                    $adminId,
                    "Payment for installment #{$repayment->installment_number} of loan #{$loan->id} needs a loan officer.",
                    route('admin.loans.show', $loan)
                );
            }
        }
    }

    public function markAsPaid(
        LoanApplication $loan,
        LoanRepayment $repayment,
        User $reviewer
    ): void {
        DB::transaction(function () use ($loan, $repayment, $reviewer) {
            $lockedLoan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($lockedLoan->assigned_reviewer_id !== $reviewer->id) {
                abort(403);
            }

            if ($lockedLoan->status !== 'disbursed') {
                throw ValidationException::withMessages([
                    'repayment' => 'Repayments can only be recorded for disbursed loans.',
                ]);
            }

            $lockedRepayment = LoanRepayment::lockForUpdate()->findOrFail($repayment->id);

            if ($lockedRepayment->loan_application_id !== $lockedLoan->id) {
                abort(404);
            }

            if ($lockedRepayment->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'repayment' => 'Only submitted payments can be confirmed.',
                ]);
            }

            $lockedRepayment->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        });

        $this->sendNotification(
            $loan->customer->user->id,
            "Payment for installment #{$repayment->installment_number} of loan #{$loan->id} was confirmed.",
            route('customer.loans.show', $loan)
        );
    }

    private function sendNotification(int $userId, string $message, string $url): void
    {
        CreateLoanNotification::dispatch($userId, $message, $url);
    }
}
