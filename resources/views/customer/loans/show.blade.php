@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <h1 class="page-title mb-0">Loan Application #{{ $loan->id }}</h1>
                <span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
            </div>
            <p class="page-subtitle">Submitted on {{ $loan->application_date->format('d M Y') }}</p>
        </div>
        <a href="{{ route('customer.loans.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-file-earmark-text me-2"></i>Loan Details</div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value">{{ number_format($loan->amount, 2) }} MMK</span></div>
            <div class="detail-item"><span class="detail-label">Term</span><span class="detail-value">{{ $loan->term_months }} months</span></div>
            <div class="detail-item"><span class="detail-label">Interest Rate</span><span class="detail-value">{{ $loan->interest_rate }}%</span></div>
            <div class="detail-item"><span class="detail-label">Reviewer</span><span class="detail-value">{{ $loan->assignedReviewer ? $loan->assignedReviewer->name : 'Not assigned' }}</span></div>
            <div class="detail-item"><span class="detail-label">Approved At</span><span class="detail-value">{{ $loan->approved_at ? $loan->approved_at->format('d M Y H:i') : '-' }}</span></div>
            <div class="detail-item"><span class="detail-label">Rejected At</span><span class="detail-value">{{ $loan->rejected_at ? $loan->rejected_at->format('d M Y H:i') : '-' }}</span></div>
            <div class="detail-item"><span class="detail-label">Cancelled At</span><span class="detail-value">{{ $loan->cancelled_at ? $loan->cancelled_at->format('d M Y H:i') : '-' }}</span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></div>
            <div class="detail-item full"><span class="detail-label">Purpose</span><span class="detail-value">{{ $loan->purpose }}</span></div>
            <div class="detail-item full"><span class="detail-label">Supporting Notes</span><span class="detail-value">{{ $loan->supporting_notes ?: '-' }}</span></div>
            <div class="detail-item full"><span class="detail-label">Decision Notes</span><span class="detail-value">{{ $loan->decision_notes ?: '-' }}</span></div>
        </div>
    </div>

    <x-repayment-summary :summary="$summary" />

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>Repayment Schedule</span>
            <i class="bi bi-calendar3 text-muted"></i>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Installment</th>
                        <th>Due Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($repayments as $repayment)
                        <tr>
                            <td class="fw-bold">#{{ $repayment->installment_number }}</td>
                            <td>{{ $repayment->due_date->format('d M Y') }}</td>
                            <td>{{ number_format($repayment->amount, 2) }} MMK</td>
                            <td><span class="status-badge status-{{ $repayment->status }}">{{ ucfirst($repayment->status) }}</span></td>
                            <td>
                                @if ($repayment->payment_screenshot)
                                    <div>{{ number_format($repayment->paid_amount, 2) }} MMK</div>
                                    <a href="{{ route('repayments.proof', $repayment) }}" target="_blank">View screenshot</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if ($loan->status === 'disbursed' && $repayment->status === 'pending')
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal{{ $repayment->id }}">
                                        Pay
                                    </button>
                                @elseif ($loan->status === 'approved' && $repayment->status === 'pending')
                                    <span class="text-muted">Waiting for disbursement</span>
                                @elseif ($repayment->status === 'submitted')
                                    <span class="text-muted">Waiting for confirmation</span>
                                @elseif ($repayment->status === 'paid')
                                    <span class="text-success">Confirmed</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">No repayment schedule available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $repayments->links() }}</div>

    @if ($loan->status === 'disbursed')
        @foreach ($repayments as $repayment)
            @if ($repayment->status === 'pending')
                <div class="modal fade" id="paymentModal{{ $repayment->id }}" tabindex="-1" aria-labelledby="paymentModalLabel{{ $repayment->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('customer.repayments.store', [$loan, $repayment]) }}" enctype="multipart/form-data" class="repayment-form">
                                @csrf
                                <div class="modal-header">
                                    <h2 class="modal-title fs-5" id="paymentModalLabel{{ $repayment->id }}">Pay Installment #{{ $repayment->installment_number }}</h2>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="amount{{ $repayment->id }}" class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="amount" id="amount{{ $repayment->id }}" value="{{ $repayment->amount }}" step="0.01" min="1" class="form-control" required>
                                            <span class="input-group-text">MMK</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="paymentScreenshot{{ $repayment->id }}" class="form-label">Payment Screenshot <span class="text-danger">*</span></label>
                                        <input type="file" name="payment_screenshot" id="paymentScreenshot{{ $repayment->id }}" accept=".jpg,.jpeg,.png" class="form-control" required>
                                        <div class="form-text">JPG or PNG, maximum 2 MB.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary repayment-button">Submit Payment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

<script>
    document.querySelectorAll('.repayment-form').forEach(function (form) {
        form.addEventListener('submit', function () {
            const button = form.querySelector('.repayment-button');
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';
        });
    });
</script>
@endsection
