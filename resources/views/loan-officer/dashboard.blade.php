@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>
            <p class="page-subtitle">Review assigned loans and keep repayments up to date.</p>
        </div>
        <a href="{{ route('loan-officer.loans.index') }}" class="btn btn-primary"><i class="bi bi-clipboard-check me-2"></i>Assigned Loans</a>
    </div>

    <div class="row g-3 mb-4">
        <a href="{{ route('loan-officer.loans.index') }}" class="col-sm-6 col-xl-3 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Total Assigned</div>
                        <div class="metric-value">{{ $totalAssigned }}</div>
                        <div class="metric-note">View all assigned loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-files"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('loan-officer.loans.index', ['status' => 'pending']) }}" class="col-sm-6 col-xl-3 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Pending</div>
                        <div class="metric-value">{{ $pendingLoans }}</div>
                        <div class="metric-note">View loans waiting for a decision</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-hourglass-split"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('loan-officer.loans.index', ['status' => 'active']) }}" class="col-sm-6 col-xl-3 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Active</div>
                        <div class="metric-value">{{ $activeLoans }}</div>
                        <div class="metric-note">View approved or disbursed loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-activity"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('loan-officer.loans.index', ['status' => 'closed']) }}" class="col-sm-6 col-xl-3 text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Completed</div>
                        <div class="metric-value">{{ $completedLoans }}</div>
                        <div class="metric-note">View closed loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-check2-circle"></i></span>
                </div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Recent Assigned Loans</span>
            <a href="{{ route('loan-officer.loans.index') }}" class="small">View all</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Customer</th><th>Amount</th><th>Status</th><th>Application Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($latestLoans as $loan)
                        <tr>
                            <td class="fw-bold">{{ $loan->customer->user->name }}</td>
                            <td>{{ number_format($loan->amount, 2) }} MMK</td>
                            <td><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td class="text-end"><a href="{{ route('loan-officer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">No assigned loans found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
