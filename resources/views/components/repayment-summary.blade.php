<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div>
                    <span class="metric-label">Total Interest</span>
                    <div class="metric-value">{{ number_format($summary['total_interest'], 2) }} MMK</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div>
                    <span class="metric-label">Total Payable</span>
                    <div class="metric-value">{{ number_format($summary['total_payable'], 2) }} MMK</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card metric-card h-100">
            <div class="card-body">
                <div>
                    <span class="metric-label">Monthly Installment</span>
                    <div class="metric-value">{{ number_format($summary['monthly_installment'], 2) }} MMK</div>
                </div>
            </div>
        </div>
    </div>
</div>
