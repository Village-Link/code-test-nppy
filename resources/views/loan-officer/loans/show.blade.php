@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h1 class="page-title mb-0">Assigned Loan #{{ $loan->id }}</h1>
                <span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
            </div>
            <p class="page-subtitle">Review the assigned loan and update its repayments.</p>
        </div>
        <a href="{{ route('loan-officer.loans.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Loan Details</div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Customer</span><span class="detail-value">{{ $loan->customer->user->name }}</span></div>
            <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value">{{ $loan->customer->user->email }}</span></div>
            <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value">{{ $loan->customer->phone }}</span></div>
            <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value">{{ number_format($loan->amount, 2) }} MMK</span></div>
            <div class="detail-item"><span class="detail-label">Term</span><span class="detail-value">{{ $loan->term_months }} months</span></div>
            <div class="detail-item"><span class="detail-label">Interest Rate</span><span class="detail-value">{{ $loan->interest_rate }}%</span></div>
            <div class="detail-item"><span class="detail-label">Application Date</span><span class="detail-value">{{ $loan->application_date->format('d M Y') }}</span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></div>
            <div class="detail-item full"><span class="detail-label">Purpose</span><span class="detail-value">{{ $loan->purpose }}</span></div>
            <div class="detail-item full"><span class="detail-label">Supporting Notes</span><span class="detail-value">{{ $loan->supporting_notes ?: '-' }}</span></div>
            <div class="detail-item full"><span class="detail-label">Admin Decision Notes</span><span class="detail-value">{{ $loan->decision_notes ?: '-' }}</span></div>
        </div>
    </div>

    <x-repayment-summary :summary="$summary" />

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between"><span>Repayment Schedule</span><i class="bi bi-calendar3 text-muted"></i></div>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Installment</th><th>Due Date</th><th>Amount</th><th>Submitted</th><th>Screenshot</th><th>Status</th><th>Paid At</th><th></th></tr></thead>
                <tbody>
                    @forelse ($repayments as $repayment)
                        <tr>
                            <td class="fw-bold">#{{ $repayment->installment_number }}</td>
                            <td>{{ $repayment->due_date->format('d M Y') }}</td>
                            <td>{{ number_format($repayment->amount, 2) }} MMK</td>
                            <td>{{ $repayment->paid_amount ? number_format($repayment->paid_amount, 2).' MMK' : '-' }}</td>
                            <td>
                                @if ($repayment->payment_screenshot)
                                    <a href="{{ route('repayments.proof', $repayment) }}" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td><span class="status-badge status-{{ $repayment->status }}">{{ ucfirst($repayment->status) }}</span></td>
                            <td>{{ $repayment->paid_at ? $repayment->paid_at->format('d M Y H:i') : '-' }}</td>
                            <td class="text-end">
                                @if ($repayment->status === 'submitted' && $loan->status === 'disbursed')
                                    <form method="POST" action="{{ route('loan-officer.repayments.paid', [$loan, $repayment]) }}" class="payment-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success payment-button">
                                            Confirm Paid
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="empty-state">No repayment schedule available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $repayments->links() }}</div>
</div>

<script>
    document.querySelectorAll('.payment-form').forEach(function (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            const result = await window.Swal.fire({
                icon: 'question',
                title: 'Confirm payment?',
                text: 'This installment will be marked as paid.',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#198754'
            });

            if (!result.isConfirmed) {
                return;
            }

            const button = form.querySelector('.payment-button');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';
            form.submit();
        });
    });
</script>
@endsection
