@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h1 class="page-title mb-0">Loan Application #{{ $loan->id }}</h1>
                <span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span>
            </div>
            <p class="page-subtitle">Review customer details and manage the loan decision.</p>
        </div>
        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card mb-4">
        <div class="card-header"><i class="bi bi-person-lines-fill me-2"></i>Application Details</div>
        <div class="detail-grid">
            <div class="detail-item"><span class="detail-label">Customer</span><span class="detail-value">{{ $loan->customer->user->name }}</span></div>
            <div class="detail-item"><span class="detail-label">Email</span><span class="detail-value">{{ $loan->customer->user->email }}</span></div>
            <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value">{{ $loan->customer->phone }}</span></div>
            <div class="detail-item"><span class="detail-label">Address</span><span class="detail-value">{{ $loan->customer->address }}</span></div>
            <div class="detail-item"><span class="detail-label">Amount</span><span class="detail-value">{{ number_format($loan->amount, 2) }} MMK</span></div>
            <div class="detail-item"><span class="detail-label">Term</span><span class="detail-value">{{ $loan->term_months }} months</span></div>
            <div class="detail-item"><span class="detail-label">Interest Rate</span><span class="detail-value">{{ $loan->interest_rate }}%</span></div>
            <div class="detail-item"><span class="detail-label">Reviewer</span><span class="detail-value">{{ $loan->assignedReviewer ? $loan->assignedReviewer->name : 'Not assigned' }}</span></div>
            <div class="detail-item"><span class="detail-label">Application Date</span><span class="detail-value">{{ $loan->application_date->format('d M Y') }}</span></div>
            <div class="detail-item"><span class="detail-label">Status</span><span class="status-badge status-{{ $loan->status }}">{{ ucfirst($loan->status) }}</span></div>
            <div class="detail-item full"><span class="detail-label">Purpose</span><span class="detail-value">{{ $loan->purpose }}</span></div>
            <div class="detail-item full"><span class="detail-label">Supporting Notes</span><span class="detail-value">{{ $loan->supporting_notes ?: '-' }}</span></div>
            <div class="detail-item full"><span class="detail-label">Decision Notes</span><span class="detail-value">{{ $loan->decision_notes ?: '-' }}</span></div>
        </div>
    </div>

    <x-repayment-summary :summary="$summary" />
    <x-repayment-schedule :repayments="$repayments" />
    <div class="mt-3 mb-4">{{ $repayments->links() }}</div>

    @if ($loan->status === 'pending')
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-person-check me-2"></i>Assign Loan Officer</div>
                    <div class="card-body">
                        @if ($officers->isEmpty())
                            <div class="empty-state">No loan officers are available.</div>
                        @else
                            <form method="POST" action="{{ route('admin.loans.assign', $loan) }}" class="loading-form">
                                @csrf
                                @method('PATCH')
                                <label for="assigned_reviewer_id" class="form-label">Loan Officer <span class="text-danger">*</span></label>
                                <select name="assigned_reviewer_id" id="assigned_reviewer_id" class="form-select mb-3 @error('assigned_reviewer_id') is-invalid @enderror" required>
                                    <option value="">Select Loan Officer</option>
                                    @foreach ($officers as $officer)
                                        <option value="{{ $officer->id }}" @selected(old('assigned_reviewer_id', $loan->assigned_reviewer_id) == $officer->id)>{{ $officer->name }} - {{ $officer->email }}</option>
                                    @endforeach
                                </select>
                                @error('assigned_reviewer_id')<div class="invalid-feedback d-block mb-3">{{ $message }}</div>@enderror
                                <button type="submit" class="btn btn-primary action-button w-100">Assign Officer</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-check2-square me-2"></i>Admin Decision</div>
                    <div class="card-body">
                        <form method="POST" class="loading-form">
                            @csrf
                            @method('PATCH')
                            <label for="decision_notes" class="form-label">Decision Notes <span class="text-muted fw-normal">(Required for rejection)</span></label>
                            <textarea name="decision_notes" id="decision_notes" rows="4" class="form-control mb-3 @error('decision_notes') is-invalid @enderror" placeholder="Add a reason or review note.">{{ old('decision_notes') }}</textarea>
                            @error('decision_notes')<div class="invalid-feedback d-block mb-3">{{ $message }}</div>@enderror
                            @if (! $loan->assigned_reviewer_id)
                                <p class="small text-muted">Assign a loan officer before approving this application.</p>
                            @endif
                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" formaction="{{ route('admin.loans.approve', $loan) }}" class="btn btn-success action-button flex-grow-1" @disabled(! $loan->assigned_reviewer_id)>Approve</button>
                                <button type="submit" formaction="{{ route('admin.loans.reject', $loan) }}" class="btn btn-danger action-button flex-grow-1">Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @elseif ($loan->status === 'approved')
        <div class="card">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">Ready for Disbursement</h2>
                    <p class="text-muted mb-0">Confirm after the approved amount has been released.</p>
                </div>
                <form method="POST" action="{{ route('admin.loans.disburse', $loan) }}" class="loading-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary action-button">Mark as Disbursed</button>
                </form>
            </div>
        </div>
    @elseif ($loan->status === 'disbursed')
        <div class="card">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <h2 class="h5 mb-1">Close Loan</h2>
                    <p class="text-muted mb-0">All installments must be paid before closing.</p>
                </div>
                <form method="POST" action="{{ route('admin.loans.close', $loan) }}" class="loading-form">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-dark action-button" @disabled($hasUnpaidRepayments)>
                        Close Loan
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    document.querySelectorAll('.loading-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            form.querySelectorAll('.action-button').forEach(function (button) {
                button.disabled = true;
            });
            event.submitter.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Processing...';
        });
    });
</script>
@endsection
