<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRepaymentRequest;
use App\Models\LoanApplication;
use App\Models\LoanRepayment;
use App\Services\LoanRepaymentService;
use Illuminate\Http\Request;

class RepaymentController extends Controller
{
    private $loanRepaymentService;

    public function __construct(LoanRepaymentService $loanRepaymentService)
    {
        $this->loanRepaymentService = $loanRepaymentService;
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:submitted,paid'],
        ]);

        $customer = $request->user()->customer;

        if (! $customer) {
            abort(403);
        }

        $loanIds = $customer->loanApplications()->pluck('id');

        $query = LoanRepayment::with('loanApplication')
            ->whereIn('loan_application_id', $loanIds)
            ->whereIn('status', ['submitted', 'paid']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $repayments = $query
            ->latest('submitted_at')
            ->latest('paid_at')
            ->paginate(10)
            ->withQueryString();

        return view('customer.repayments.index', compact('repayments', 'filters'));
    }

    public function store(StoreRepaymentRequest $request, LoanApplication $loan, LoanRepayment $repayment)
    {
        $customer = $request->user()->customer;

        if (! $customer || $loan->customer_id !== $customer->id) {
            abort(403);
        }

        if ($repayment->loan_application_id !== $loan->id) {
            abort(404);
        }

        $this->loanRepaymentService->submit(
            $loan,
            $repayment,
            $customer,
            (float) $request->input('amount'),
            $request->file('payment_screenshot')
        );

        return redirect()
            ->route('customer.loans.show', $loan)
            ->with('success', 'Payment submitted successfully.');
    }
}
