@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>
            <p class="page-subtitle">Track your applications and repayment progress.</p>
        </div>
        <a href="{{ route('customer.loans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Application</a>
    </div>

    <div class="row g-3 mb-4">
        <a href="{{ route('customer.loans.index') }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Applications</div>
                        <div class="metric-value">{{ $totalApplications }}</div>
                        <div class="metric-note">View all submitted loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-files"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('customer.loans.index', ['status' => 'pending']) }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Pending</div>
                        <div class="metric-value">{{ $pendingApplications }}</div>
                        <div class="metric-note">View loans waiting for review</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-hourglass-split"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('customer.loans.index', ['status' => 'active']) }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Approved / Active</div>
                        <div class="metric-value">{{ $approvedApplications }}</div>
                        <div class="metric-note">View approved and active loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-check2-circle"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('customer.loans.index') }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Total Repayment</div>
                        <div class="metric-value fs-5">{{ number_format($totalRepaymentAmount, 2) }}</div>
                        <div class="metric-note">MMK scheduled</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-receipt"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('customer.loans.index', ['repayment' => 'paid']) }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Paid</div>
                        <div class="metric-value fs-5">{{ number_format($paidRepaymentAmount, 2) }}</div>
                        <div class="metric-note">View loans with paid installments</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-wallet2"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('customer.loans.index', ['repayment' => 'outstanding']) }}" class="col-sm-6 col-xl-4 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Outstanding</div>
                        <div class="metric-value fs-5">{{ number_format($outstandingAmount, 2) }}</div>
                        <div class="metric-note">View loans with remaining payments</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-calendar2-week"></i></span>
                </div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Recent Applications</span>
            <a href="{{ route('customer.loans.index') }}" class="small">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Amount</th><th>Status</th><th>Application Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($latestApplications as $loan)
                        <tr>
                            <td class="fw-bold">{{ number_format($loan->amount, 2) }} MMK</td>
                            <td><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td class="text-end"><a href="{{ route('customer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
