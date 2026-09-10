@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Assigned Loan Applications</h1>
            <p class="page-subtitle">Filter assigned loans and manage repayment records.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('loan-officer.loans.index') }}" class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter Assigned Loans</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="search" class="form-label">Search Customer</label>
                    <input type="search" name="search" id="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, email, or phone">
                </div>
                <div class="col-md-5">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('loan-officer.loans.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between"><span>Assigned Loans</span><span class="text-muted small">{{ $loans->total() }} records</span></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Customer</th><th>Amount</th><th>Term</th><th>Status</th><th>Application Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td><strong>{{ $loan->customer->user->name }}</strong><div class="small text-muted">{{ $loan->customer->user->email }}</div></td>
                            <td>{{ number_format($loan->amount, 2) }} MMK</td>
                            <td>{{ $loan->term_months }} months</td>
                            <td><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td class="text-end"><a href="{{ route('loan-officer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">No assigned loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $loans->links() }}</div>
</div>
@endsection
