<?php

namespace App\Http\Controllers\LoanOfficer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $totalAssigned = $user->assignedLoanApplications()->count();
        $pendingLoans = $user->assignedLoanApplications()
            ->where('status', 'pending')
            ->count();
        $activeLoans = $user->assignedLoanApplications()
            ->whereIn('status', ['approved', 'disbursed'])
            ->count();
        $completedLoans = $user->assignedLoanApplications()
            ->where('status', 'closed')
            ->count();
        $latestLoans = $user->assignedLoanApplications()
            ->with('customer.user')
            ->latest('application_date')
            ->limit(5)
            ->get();

        return view('loan-officer.dashboard', compact(
            'totalAssigned',
            'pendingLoans',
            'activeLoans',
            'completedLoans',
            'latestLoans'
        ));
    }
}
