@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Repayment History</h1>
            <p class="page-subtitle">Review your submitted and confirmed repayments.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('customer.repayments.index') }}" class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter Repayments</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Repayments</option>
                        <option value="submitted" @selected(($filters['status'] ?? '') === 'submitted')>Submitted</option>
                        <option value="paid" @selected(($filters['status'] ?? '') === 'paid')>Paid</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('customer.repayments.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Previous Repayments</span>
            <span class="text-muted small">{{ $repayments->total() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Loan</th>
                        <th>Installment</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th>Paid At</th>
                        <th>Screenshot</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repayments as $repayment)
                        <tr>
                            <td class="fw-bold">#{{ $repayment->loan_application_id }}</td>
                            <td>#{{ $repayment->installment_number }}</td>
                            <td>{{ $repayment->due_date->format('d M Y') }}</td>
                            <td>{{ number_format($repayment->paid_amount ?? $repayment->amount, 2) }} MMK</td>
                            <td><span class="status-badge status-{{ $repayment->status }}">{{ ucfirst($repayment->status) }}</span></td>
                            <td>{{ $repayment->submitted_at ? $repayment->submitted_at->format('d M Y H:i') : '-' }}</td>
                            <td>{{ $repayment->paid_at ? $repayment->paid_at->format('d M Y H:i') : '-' }}</td>
                            <td>
                                @if ($repayment->payment_screenshot)
                                    <a href="{{ route('repayments.proof', $repayment) }}" target="_blank">View</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('customer.loans.show', $repayment->loanApplication) }}" class="btn btn-sm btn-outline-primary">View Loan</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="empty-state">No repayment history found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $repayments->links() }}</div>
</div>
@endsection
