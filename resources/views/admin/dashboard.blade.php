@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>
            <p class="page-subtitle">Review applications, approvals, and loan activity from one place.</p>
        </div>
        <a href="{{ route('admin.loans.index') }}" class="btn btn-primary"><i class="bi bi-file-earmark-text me-2"></i>View Applications</a>
    </div>

    <div class="row g-3 mb-4">
        <a href="{{ route('admin.loans.index') }}" class="col-sm-6 col-xl text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Total Applications</div>
                        <div class="metric-value">{{ $totalApplications }}</div>
                        <div class="metric-note">View all submitted loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-files"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'pending']) }}" class="col-sm-6 col-xl text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Pending Review</div>
                        <div class="metric-value">{{ $pendingApplications }}</div>
                        <div class="metric-note">View loans waiting for a decision</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-hourglass-split"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'active']) }}" class="col-sm-6 col-xl text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Approved Amount</div>
                        <div class="metric-value fs-5">{{ number_format($approvedAmount, 2) }}</div>
                        <div class="metric-note">View approved and active loans</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-cash-stack"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.loans.index', ['status' => 'rejected']) }}" class="col-sm-6 col-xl text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Rejected</div>
                        <div class="metric-value">{{ $rejectedCount }}</div>
                        <div class="metric-note">View declined applications</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-x-circle"></i></span>
                </div>
            </div>
        </a>
        <a href="{{ route('admin.loans.index') }}" class="col-sm-6 col-xl text-decoration-none text-reset">
            <div class="card metric-card h-100">
                <div class="card-body">
                    <div>
                        <div class="metric-label">Average Loan</div>
                        <div class="metric-value fs-5">{{ number_format($averageLoanAmount, 2) }}</div>
                        <div class="metric-note">View all applications</div>
                    </div>
                    <span class="metric-icon"><i class="bi bi-graph-up"></i></span>
                </div>
            </div>
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Applications by Status</span>
            <i class="bi bi-pie-chart text-muted"></i>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($applicationsByStatus as $status => $total)
                    <a href="{{ route('admin.loans.index', ['status' => $status]) }}" class="col-sm-6 col-lg-4 col-xl text-decoration-none text-reset">
                        <div class="d-flex align-items-center justify-content-between border rounded-3 p-3 h-100">
                            <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span>
                            <strong class="fs-4">{{ $total }}</strong>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
