<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanApplicationRequest;
use App\Models\Customer;
use App\Models\LoanApplication;
use App\Services\LoanApplicationService;
use App\Services\RepaymentScheduleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(array_merge(config('loan.statuses'), ['active']))],
            'repayment' => ['nullable', Rule::in(['paid', 'outstanding'])],
        ]);

        $customer = $this->customer($request);
        $query = $customer->loanApplications();

        if (! empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->whereIn('status', ['approved', 'disbursed', 'closed']);
            } else {
                $query->where('status', $filters['status']);
            }
        }

        if (($filters['repayment'] ?? null) === 'paid') {
            $query->whereHas('repayments', function ($repaymentQuery) {
                $repaymentQuery->where('status', 'paid');
            });
        }

        if (($filters['repayment'] ?? null) === 'outstanding') {
            $query->whereHas('repayments', function ($repaymentQuery) {
                $repaymentQuery->where('status', '!=', 'paid');
            });
        }

        $loans = $query
            ->withSum('repayments as total_repayment_amount', 'amount')
            ->withSum('paidRepayments as paid_repayment_amount', 'amount')
            ->latest('application_date')
            ->paginate(10)
            ->withQueryString();

        $statuses = config('loan.statuses');

        return view('customer.loans.index', compact('loans', 'statuses', 'filters'));
    }

    public function create()
    {
        return view('customer.loans.create');
    }

    public function show(Request $request, LoanApplication $loan)
    {
        $customer = $this->customer($request);

        if ($loan->customer_id !== $customer->id) {
            abort(403);
        }

        $loan->load('assignedReviewer');
        $repayments = $loan->repayments()
            ->orderBy('installment_number')
            ->paginate(10);
        $summary = $this->repaymentScheduleService->summary(
            (float) $loan->amount,
            $loan->term_months,
            (float) $loan->interest_rate
        );

        return view('customer.loans.show', compact('loan', 'summary', 'repayments'));
    }

    public function store(StoreLoanApplicationRequest $request)
    {
        $this->loanApplicationService->create(
            $this->customer($request),
            $request->validated()
        );

        return redirect()
            ->route('customer.loans.index')
            ->with('success', 'Loan application created successfully.');
    }

    public function cancel(Request $request, LoanApplication $loan)
    {
        $customer = $this->customer($request);

        if ($loan->customer_id !== $customer->id) {
            abort(403);
        }

        $this->loanApplicationService->cancel($loan, $customer);

        return redirect()
            ->route('customer.loans.index')
            ->with('success', 'Loan application cancelled successfully.');
    }

    private function customer(Request $request): Customer
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            abort(403);
        }

        return $customer;
    }
}
