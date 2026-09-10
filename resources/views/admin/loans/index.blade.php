@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Loan Applications</h1>
            <p class="page-subtitle">Search, filter, and review every customer application.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.loans.index') }}" class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter Applications</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6 col-xl-4">
                    <label for="search" class="form-label">Search customer</label>
                    <input type="search" name="search" id="search" value="{{ $filters['search'] ?? '' }}" class="form-control" placeholder="Name, email, or phone">
                </div>
                <div class="col-md-6 col-xl-4">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Approved / Active</option>
                        @foreach ($statuses as $statusOption)
                            <option value="{{ $statusOption }}" @selected(($filters['status'] ?? '') === $statusOption)>{{ ucfirst($statusOption) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 col-xl-4">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select name="customer_id" id="customer_id" class="form-select">
                        <option value="">All Customers</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(($filters['customer_id'] ?? '') == $customer->id)>{{ $customer->user->name }} - {{ $customer->user->email }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label for="min_amount" class="form-label">Minimum amount</label>
                    <input type="number" name="min_amount" id="min_amount" value="{{ $filters['min_amount'] ?? '' }}" min="0" step="0.01" class="form-control">
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label for="max_amount" class="form-label">Maximum amount</label>
                    <input type="number" name="max_amount" id="max_amount" value="{{ $filters['max_amount'] ?? '' }}" min="0" step="0.01" class="form-control">
                </div>
                <div class="col-sm-6 col-xl-3">
                    <label for="per_page" class="form-label">Per page</label>
                    <select name="per_page" id="per_page" class="form-select">
                        @foreach ([10, 25, 50] as $perPage)
                            <option value="{{ $perPage }}" @selected(($filters['per_page'] ?? 10) == $perPage)>{{ $perPage }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-6 col-xl-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Apply Filter</button>
                    <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between"><span>Application Results</span><span class="text-muted small">{{ $loans->total() }} records</span></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Customer</th><th>Amount</th><th>Status</th><th>Reviewer</th><th>Application Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td><strong>{{ $loan->customer->user->name }}</strong><div class="small text-muted">{{ $loan->customer->user->email }}</div></td>
                            <td class="fw-bold">{{ number_format($loan->amount, 2) }} MMK</td>
                            <td><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>{{ $loan->assignedReviewer ? $loan->assignedReviewer->name : 'Not assigned' }}</td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td class="text-end"><a href="{{ route('admin.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $loans->links() }}</div>
</div>
@endsection
