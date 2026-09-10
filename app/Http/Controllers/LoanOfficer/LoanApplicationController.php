<?php

namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Services\LoanRepaymentService;
use App\Services\RepaymentScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LoanApplicationController extends Controller
{
    private $loanRepaymentService;

    private $repaymentScheduleService;

    public function __construct(
        LoanRepaymentService $loanRepaymentService,
        RepaymentScheduleService $repaymentScheduleService
    ) {
        $this->loanRepaymentService = $loanRepaymentService;
        $this->repaymentScheduleService = $repaymentScheduleService;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_merge(config('loan.statuses'), ['active']))],
        ]);

        $query = $request->user()
            ->assignedLoanApplications()
            ->with('customer.user');

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
                $query->whereIn('status', ['approved', 'disbursed']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        $loans = $query
            ->latest('application_date')
            ->paginate(10)
            ->withQueryString();

        $statuses = config('loan.statuses');

        return view('loan-officer.loans.index', compact('loans', 'statuses', 'filters'));
    }

    public function show(Request $request, LoanApplication $loan)
    {
        $this->ensureAssignedReviewer($request, $loan);

        $loan->load('customer.user');
        $repayments = $loan->repayments()
            ->orderBy('installment_number')
            ->paginate(10);
        $summary = $this->repaymentScheduleService->summary(
            (float) $loan->amount,
            $loan->term_months,
            (float) $loan->interest_rate
        );

        return view('loan-officer.loans.show', compact('loan', 'summary', 'repayments'));
    }

    public function markRepaymentPaid(Request $request, LoanApplication $loan, LoanRepayment $repayment)
    {
        $this->ensureAssignedReviewer($request, $loan);

        if ($repayment->loan_application_id !== $loan->id) {
            abort(404);
        }

        $this->loanRepaymentService->markAsPaid(
            $loan,
            $repayment,
            $request->user()
        );

        return redirect()
            ->route('loan-officer.loans.show', $loan)
            ->with('success', 'Repayment recorded successfully.');
    }

    private function ensureAssignedReviewer(Request $request, LoanApplication $loan): void
    {
        if ($loan->assigned_reviewer_id !== $request->user()->id) {
            abort(403);
        }
    }
}
