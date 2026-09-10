<?php

namespace App\Http\Controllers;

use App\Models\LoanRepayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RepaymentProofController extends Controller
{
    public function show(Request $request, LoanRepayment $repayment)
    {
        $user = $request->user();
        $loan = $repayment->loanApplication()->with('customer')->firstOrFail();
        $canView = false;

        if ($user->hasRole('admin')) {
            $canView = true;
        }

        if ($user->hasRole('loan_officer') && $loan->assigned_reviewer_id === $user->id) {
            $canView = true;
        }

        if ($user->hasRole('customer') && $loan->customer->user_id === $user->id) {
            $canView = true;
        }

        if (! $canView) {
            abort(403);
        }

        if (! $repayment->payment_screenshot) {
            abort(404);
        }

        if (! Storage::disk('local')->exists($repayment->payment_screenshot)) {
            abort(404);
        }

        return Storage::disk('local')->response($repayment->payment_screenshot);
    }
}
