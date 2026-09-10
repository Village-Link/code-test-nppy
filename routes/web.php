<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LoanApplicationController as AdminLoanApplicationController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\LoanApplicationController as CustomerLoanApplicationController;
use App\Http\Controllers\Customer\RepaymentController as CustomerRepaymentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanOfficer\DashboardController as LoanOfficerDashboardController;
use App\Http\Controllers\LoanOfficer\LoanApplicationController as LoanOfficerLoanApplicationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\RepaymentProofController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }

    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/repayments/{repayment}/proof', [RepaymentProofController::class, 'show'])
        ->name('repayments.proof');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])
        ->name('notifications.read-all');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])
        ->name('notifications.read');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/loans', [AdminLoanApplicationController::class, 'index'])
            ->middleware('permission:loans.view-all')
            ->name('loans.index');

        Route::get('/loans/{loan}', [AdminLoanApplicationController::class, 'show'])
            ->middleware('permission:loans.view-all')
            ->name('loans.show');

        Route::patch('/loans/{loan}/assign', [AdminLoanApplicationController::class, 'assign'])
            ->middleware('permission:loans.assign-reviewer')
            ->name('loans.assign');

        Route::patch('/loans/{loan}/approve', [AdminLoanApplicationController::class, 'approve'])
            ->middleware('permission:loans.approve')
            ->name('loans.approve');

        Route::patch('/loans/{loan}/reject', [AdminLoanApplicationController::class, 'reject'])
            ->middleware('permission:loans.reject')
            ->name('loans.reject');

        Route::patch('/loans/{loan}/disburse', [AdminLoanApplicationController::class, 'disburse'])
            ->middleware('permission:loans.disburse')
            ->name('loans.disburse');

        Route::patch('/loans/{loan}/close', [AdminLoanApplicationController::class, 'close'])
            ->middleware('permission:loans.close')
            ->name('loans.close');
    });

Route::middleware(['auth', 'role:loan_officer'])
    ->prefix('loan-officer')
    ->name('loan-officer.')
    ->group(function () {
        Route::get('/dashboard', [LoanOfficerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('/loans', [LoanOfficerLoanApplicationController::class, 'index'])
            ->middleware('permission:loans.view-assigned')
            ->name('loans.index');

        Route::get('/loans/{loan}', [LoanOfficerLoanApplicationController::class, 'show'])
            ->middleware('permission:loans.review')
            ->name('loans.show');

        Route::patch('/loans/{loan}/repayments/{repayment}/paid', [LoanOfficerLoanApplicationController::class, 'markRepaymentPaid'])
            ->middleware('permission:repayments.record')
            ->name('repayments.paid');
    });

Route::middleware(['auth', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {
        Route::get('/dashboard', [CustomerDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('loans', CustomerLoanApplicationController::class)
            ->only(['index', 'create', 'store', 'show']);

        Route::patch('/loans/{loan}/cancel', [CustomerLoanApplicationController::class, 'cancel'])
            ->middleware('permission:loans.cancel')
            ->name('loans.cancel');

        Route::get('/repayments', [CustomerRepaymentController::class, 'index'])
            ->middleware('permission:repayments.view-own')
            ->name('repayments.index');

        Route::post('/loans/{loan}/repayments/{repayment}', [CustomerRepaymentController::class, 'store'])
            ->middleware('permission:repayments.pay-own')
            ->name('repayments.store');
    });
