@props(['repayments'])

<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span>Repayment Schedule</span>
        <i class="bi bi-calendar3 text-muted"></i>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead><tr><th>Installment</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Paid At</th></tr></thead>
            <tbody>
                @forelse ($repayments as $repayment)
                    <tr>
                        <td class="fw-bold">#{{ $repayment->installment_number }}</td>
                        <td>{{ $repayment->due_date->format('d M Y') }}</td>
                        <td>{{ number_format($repayment->amount, 2) }} MMK</td>
                        <td><span class="status-badge status-{{ $repayment->status }}">{{ ucfirst($repayment->status) }}</span></td>
                        <td>{{ $repayment->paid_at ? $repayment->paid_at->format('d M Y H:i') : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">No repayment schedule available.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
