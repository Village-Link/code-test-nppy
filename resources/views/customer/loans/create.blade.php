@extends('layouts.app')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">New Loan Application</h1>
            <p class="page-subtitle">Enter the loan details below and submit them for review.</p>
        </div>
        <a href="{{ route('customer.loans.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-2"></i>Back</a>
    </div>

    <div class="card">
        <div class="card-header"><i class="bi bi-file-earmark-plus me-2"></i>Application Details</div>
        <div class="card-body">
            <form method="POST" action="{{ route('customer.loans.store') }}" id="loanApplicationForm">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="amount" class="form-label">Loan Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="{{ config('loan.amount.min') }}" max="{{ config('loan.amount.max') }}" step="0.01" class="form-control @error('amount') is-invalid @enderror" placeholder="{{ config('loan.amount.min') }}" required>
                            <span class="input-group-text">MMK</span>
                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Minimum {{ number_format(config('loan.amount.min')) }} MMK and maximum {{ number_format(config('loan.amount.max')) }} MMK.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="term_months" class="form-label">Term <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="term_months" id="term_months" value="{{ old('term_months') }}" min="{{ config('loan.term_months.min') }}" max="{{ config('loan.term_months.max') }}" class="form-control @error('term_months') is-invalid @enderror" placeholder="12" required>
                            <span class="input-group-text">Months</span>
                            @error('term_months')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-text">Choose a repayment term from {{ config('loan.term_months.min') }} to {{ config('loan.term_months.max') }} months.</div>
                    </div>

                    <div class="col-12">
                        <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                        <input type="text" name="purpose" id="purpose" value="{{ old('purpose') }}" class="form-control @error('purpose') is-invalid @enderror" placeholder="What will this loan be used for?" required>
                        @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label for="supporting_notes" class="form-label">Supporting Notes <span class="text-muted fw-normal">(Optional)</span></label>
                        <textarea name="supporting_notes" id="supporting_notes" rows="5" class="form-control @error('supporting_notes') is-invalid @enderror" placeholder="Add any information that may help with the review.">{{ old('supporting_notes') }}</textarea>
                        @error('supporting_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="{{ route('customer.loans.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary" id="submitButton">
                        <span id="submitText"><i class="bi bi-send me-2"></i>Submit Application</span>
                        <span id="submitLoading" class="d-none"><span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Submitting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('loanApplicationForm').addEventListener('submit', function () {
        const submitButton = document.getElementById('submitButton');
        submitButton.disabled = true;
        document.getElementById('submitText').classList.add('d-none');
        document.getElementById('submitLoading').classList.remove('d-none');
    });
</script>
@endsection
