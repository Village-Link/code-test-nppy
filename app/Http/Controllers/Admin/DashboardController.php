<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoanApplication;

class DashboardController extends Controller
{
    public function index()
    {
        $totalApplications = LoanApplication::count();
        $pendingApplications = LoanApplication::where('status', 'pending')->count();
        $approvedAmount = LoanApplication::whereIn('status', ['approved', 'disbursed', 'closed'])->sum('amount');
        $rejectedCount = LoanApplication::where('status', 'rejected')->count();
        $averageLoanAmount = LoanApplication::average('amount') ?? 0;

        $applicationsByStatus = [];

        foreach (config('loan.statuses') as $status) {
            $applicationsByStatus[$status] = LoanApplication::where('status', $status)->count();
        }

        return view('admin.dashboard', compact(
            'totalApplications',
            'pendingApplications',
            'approvedAmount',
            'rejectedCount',
            'averageLoanAmount',
            'applicationsByStatus'
        ));
    }
}
