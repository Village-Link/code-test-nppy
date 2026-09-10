<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\LoanRepayment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->user()->customer;

        if (! $customer) {
            abort(403);
        }

        $totalApplications = $customer->loanApplications()->count();
        $pendingApplications = $customer->loanApplications()->where('status', 'pending')->count();
        $approvedApplications = $customer->loanApplications()
            ->whereIn('status', ['approved', 'disbursed', 'closed'])
            ->count();

        $loanIds = $customer->loanApplications()->pluck('id');

        $totalRepaymentAmount = LoanRepayment::whereIn(
            'loan_application_id',
            $loanIds
        )->sum('amount');

        $paidRepaymentAmount = LoanRepayment::whereIn(
            'loan_application_id',
            $loanIds
        )
            ->where('status', 'paid')
            ->sum('amount');
        $outstandingAmount = $totalRepaymentAmount - $paidRepaymentAmount;

        $latestApplications = $customer->loanApplications()
            ->latest('application_date')
            ->limit(5)
            ->get();

        return view('customer.dashboard', compact(
            'totalApplications',
            'pendingApplications',
            'approvedApplications',
            'totalRepaymentAmount',
            'paidRepaymentAmount',
            'outstandingAmount',
            'latestApplications'
        ));
    }
}
