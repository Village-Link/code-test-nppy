<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignLoanReviewerRequest;
use App\Http\Requests\FilterLoanApplicationRequest;
use App\Http\Requests\ReviewLoanApplicationRequest;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Models\User;
use App\Services\LoanApplicationService;
use App\Services\RepaymentScheduleService;

class LoanApplicationController extends Controller
{
    private $loanApplicationService;

    private $repaymentScheduleService;

    public function __construct(
        LoanApplicationService $loanApplicationService,
        RepaymentScheduleService $repaymentScheduleService
    ) {
        $this->loanApplicationService = $loanApplicationService;
        $this->repaymentScheduleService = $repaymentScheduleService;
    }

    public function index(FilterLoanApplicationRequest $request)
    {
        $filters = $request->validated();
        $statuses = config('loan.statuses');

        $query = LoanApplication::with(['customer.user', 'assignedReviewer']);

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->whereHas('customer', function ($customerQuery) use ($search) {
                $customerQuery
                    ->where('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereIn('status', ['approved', 'disbursed', 'closed']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        if (isset($filters['min_amount'])) {
            $query->where('amount', '>=', $filters['min_amount']);
        }

        if (isset($filters['max_amount'])) {
            $query->where('amount', '<=', $filters['max_amount']);
        }

        $loans = $query
            ->latest('application_date')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();

        $customers = Customer::with('user')
            ->get()
            ->sortBy('user.name');

        return view('admin.loans.index', compact('loans', 'statuses', 'customers', 'filters'));
    }

    public function show(LoanApplication $loan)
    {
        $loan->load(['customer.user', 'assignedReviewer']);
        $repayments = $loan->repayments()
            ->orderBy('installment_number')
            ->paginate(10);
        $hasUnpaidRepayments = $loan->repayments()
            ->where('status', '!=', 'paid')
            ->exists();

        $summary = $this->repaymentScheduleService->summary(
            (float) $loan->amount,
            $loan->term_months,
            (float) $loan->interest_rate
        );

        $officers = User::role('loan_officer')
            ->orderBy('name')
            ->get();

        return view('admin.loans.show', compact(
            'loan',
            'officers',
            'summary',
            'repayments',
            'hasUnpaidRepayments'
        ));
    }

    public function assign(AssignLoanReviewerRequest $request, LoanApplication $loan)
    {
        $this->loanApplicationService->assignReviewer(
            $loan,
            (int) $request->validated('assigned_reviewer_id')
        );

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan officer assigned successfully.');
    }

    public function approve(ReviewLoanApplicationRequest $request, LoanApplication $loan)
    {
        $this->loanApplicationService->approve(
            $loan,
            $request->validated('decision_notes')
        );

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan application approved successfully.');
    }

    public function reject(ReviewLoanApplicationRequest $request, LoanApplication $loan)
    {
        $this->loanApplicationService->reject(
            $loan,
            $request->validated('decision_notes')
        );

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan application rejected successfully.');
    }

    public function disburse(LoanApplication $loan)
    {
        $this->loanApplicationService->disburse($loan);

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan marked as disbursed successfully.');
    }

    public function close(LoanApplication $loan)
    {
        $this->loanApplicationService->close($loan);

        return redirect()
            ->route('admin.loans.show', $loan)
            ->with('success', 'Loan closed successfully.');
    }
}
