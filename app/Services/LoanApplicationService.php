<?php

namespace App\Services;

use App\Jobs\CreateLoanNotification;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoanApplicationService
{
    private $repaymentScheduleService;

    public function __construct(RepaymentScheduleService $repaymentScheduleService)
    {
        $this->repaymentScheduleService = $repaymentScheduleService;
    }

    public function create(Customer $customer, array $data): LoanApplication
    {
        $loan = DB::transaction(function () use ($customer, $data) {
            $customer = Customer::lockForUpdate()->findOrFail($customer->id);

            $hasPendingLoan = $customer->loanApplications()
                ->where('status', 'pending')
                ->exists();

            if ($hasPendingLoan) {
                throw ValidationException::withMessages([
                    'loan' => 'You already have a pending loan application.',
                ]);
            }

            return $customer->loanApplications()->create([
                'amount' => $data['amount'],
                'term_months' => $data['term_months'],
                'interest_rate' => config('loan.interest_rate'),
                'purpose' => $data['purpose'],
                'supporting_notes' => $data['supporting_notes'] ?? null,
                'status' => 'pending',
                'application_date' => today(),
            ]);
        });

        $this->notifyAdmins(
            "A new loan application #{$loan->id} was submitted.",
            route('admin.loans.show', $loan)
        );

        return $loan;
    }

    public function assignReviewer(LoanApplication $loan, int $reviewerId): void
    {
        $reviewer = User::role('loan_officer')->find($reviewerId);

        if (! $reviewer) {
            throw ValidationException::withMessages([
                'assigned_reviewer_id' => 'The selected user is not a loan officer.',
            ]);
        }

        DB::transaction(function () use ($loan, $reviewer) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'loan' => 'Only pending loan applications can be assigned.',
                ]);
            }

            $loan->update([
                'assigned_reviewer_id' => $reviewer->id,
            ]);
        });

        $this->sendNotification(
            $reviewer->id,
            "Loan application #{$loan->id} was assigned to you.",
            route('loan-officer.loans.show', $loan)
        );

        $this->sendNotification(
            $loan->customer->user->id,
            "A loan officer was assigned to your loan application #{$loan->id}.",
            route('customer.loans.show', $loan)
        );
    }

    public function approve(LoanApplication $loan, ?string $decisionNotes): void
    {
        DB::transaction(function () use ($loan, $decisionNotes) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'loan' => 'Only pending loan applications can be approved.',
                ]);
            }

            if (! $loan->assigned_reviewer_id) {
                throw ValidationException::withMessages([
                    'loan' => 'Assign a loan officer before approving this application.',
                ]);
            }

            $approvedAt = now();

            $loan->update([
                'status' => 'approved',
                'decision_notes' => $decisionNotes,
                'approved_at' => $approvedAt,
            ]);

            $this->repaymentScheduleService->createFor($loan, $approvedAt);
        });

        $this->sendNotification(
            $loan->customer->user->id,
            "Your loan application #{$loan->id} was approved.",
            route('customer.loans.show', $loan)
        );
    }

    public function reject(LoanApplication $loan, string $decisionNotes): void
    {
        DB::transaction(function () use ($loan, $decisionNotes) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'loan' => 'Only pending loan applications can be rejected.',
                ]);
            }

            $loan->update([
                'status' => 'rejected',
                'decision_notes' => $decisionNotes,
                'rejected_at' => now(),
            ]);
        });

        $this->sendNotification(
            $loan->customer->user->id,
            "Your loan application #{$loan->id} was rejected.",
            route('customer.loans.show', $loan)
        );
    }

    public function disburse(LoanApplication $loan): void
    {
        DB::transaction(function () use ($loan) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'approved') {
                throw ValidationException::withMessages([
                    'loan' => 'Only approved loans can be disbursed.',
                ]);
            }

            if (! $loan->assigned_reviewer_id) {
                throw ValidationException::withMessages([
                    'loan' => 'A loan officer must be assigned before disbursement.',
                ]);
            }

            $loan->update([
                'status' => 'disbursed',
            ]);
        });

        $this->sendNotification(
            $loan->customer->user->id,
            "Your loan #{$loan->id} was disbursed.",
            route('customer.loans.show', $loan)
        );
    }

    public function close(LoanApplication $loan): void
    {
        DB::transaction(function () use ($loan) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->status !== 'disbursed') {
                throw ValidationException::withMessages([
                    'loan' => 'Only disbursed loans can be closed.',
                ]);
            }

            $hasRepayments = $loan->repayments()->exists();
            $hasUnpaidRepayments = $loan->repayments()
                ->where('status', '!=', 'paid')
                ->exists();

            if (! $hasRepayments || $hasUnpaidRepayments) {
                throw ValidationException::withMessages([
                    'loan' => 'All installments must be paid before closing the loan.',
                ]);
            }

            $loan->update([
                'status' => 'closed',
            ]);
        });

        $this->sendNotification(
            $loan->customer->user->id,
            "Your loan #{$loan->id} was closed.",
            route('customer.loans.show', $loan)
        );
    }

    public function cancel(LoanApplication $loan, Customer $customer): void
    {
        DB::transaction(function () use ($loan, $customer) {
            $loan = LoanApplication::lockForUpdate()->findOrFail($loan->id);

            if ($loan->customer_id !== $customer->id) {
                abort(403);
            }

            if ($loan->status !== 'pending') {
                throw ValidationException::withMessages([
                    'loan' => 'Only pending loan applications can be cancelled.',
                ]);
            }

            $loan->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);
        });

        if ($loan->assigned_reviewer_id) {
            $this->sendNotification(
                $loan->assigned_reviewer_id,
                "Loan application #{$loan->id} was cancelled by the customer.",
                route('loan-officer.loans.show', $loan)
            );
        }

        $this->notifyAdmins(
            "Loan application #{$loan->id} was cancelled by the customer.",
            route('admin.loans.show', $loan)
        );
    }

    private function notifyAdmins(string $message, string $url): void
    {
        $adminIds = User::role('admin')->pluck('id');

        foreach ($adminIds as $adminId) {
            $this->sendNotification($adminId, $message, $url);
        }
    }

    private function sendNotification(int $userId, string $message, string $url): void
    {
        CreateLoanNotification::dispatch($userId, $message, $url);
    }
}
