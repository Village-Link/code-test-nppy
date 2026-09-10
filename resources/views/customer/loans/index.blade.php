@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Application History</h1>
            <p class="page-subtitle">Filter your applications and review repayment details.</p>
        </div>
        <a href="{{ route('customer.loans.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-2"></i>New Application</a>
    </div>

    <form method="GET" action="{{ route('customer.loans.index') }}" class="card mb-4">
        <div class="card-header"><i class="bi bi-funnel me-2"></i>Filter History</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-5">
                    <label for="status" class="form-label">Loan Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>Approved / Active</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="repayment" class="form-label">Repayment Status</label>
                    <select name="repayment" id="repayment" class="form-select">
                        <option value="">All Repayments</option>
                        <option value="paid" @selected(($filters['repayment'] ?? '') === 'paid')>Has Paid Installments</option>
                        <option value="outstanding" @selected(($filters['repayment'] ?? '') === 'outstanding')>Has Outstanding Installments</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                    <a href="{{ route('customer.loans.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Applications</span>
            <span class="text-muted small">{{ $loans->total() }} records</span>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Amount</th><th>Term</th><th>Status</th><th>Total Repayment</th><th>Paid</th><th>Outstanding</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($loans as $loan)
                        @php
                            $totalRepayment = (float) ($loan->total_repayment_amount ?? 0);
                            $paidRepayment = (float) ($loan->paid_repayment_amount ?? 0);
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ number_format($loan->amount, 2) }} MMK</td>
                            <td>{{ $loan->term_months }} months</td>
                            <td><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></td>
                            <td>{{ number_format($totalRepayment, 2) }} MMK</td>
                            <td>{{ number_format($paidRepayment, 2) }} MMK</td>
                            <td>{{ number_format($totalRepayment - $paidRepayment, 2) }} MMK</td>
                            <td>{{ $loan->application_date->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('customer.loans.show', $loan) }}" class="btn btn-sm btn-outline-primary">View</a>
                                    @if ($loan->status === 'pending')
                                        <form method="POST" action="{{ route('customer.loans.cancel', $loan) }}" class="cancel-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-danger cancel-button">Cancel</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">No loan applications found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $loans->links() }}</div>
</div>

<script>
    document.querySelectorAll('.cancel-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const result = await window.Swal.fire({
                icon: 'warning',
                title: 'Cancel loan application?',
                text: 'This action cannot be undone.',
                showCancelButton: true,
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'Keep application',
                confirmButtonColor: '#b42318'
            });

            if (!result.isConfirmed) {
                return;
            }

            const button = form.querySelector('.cancel-button');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Cancelling...';
            form.submit();
        });
    });
</script>
@endsection
