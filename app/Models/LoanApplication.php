<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'assigned_reviewer_id',
        'amount',
        'term_months',
        'interest_rate',
        'purpose',
        'supporting_notes',
        'decision_notes',
        'status',
        'application_date',
        'approved_at',
        'rejected_at',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'application_date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedReviewer()
    {
        return $this->belongsTo(User::class);
    }

    public function repayments()
    {
        return $this->hasMany(LoanRepayment::class);
    }

    public function paidRepayments()
    {
        return $this->hasMany(LoanRepayment::class)->where('status', 'paid');
    }
}
