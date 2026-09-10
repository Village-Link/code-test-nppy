<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function assignedLoanApplications()
    {
        return $this->hasMany(LoanApplication::class, 'assigned_reviewer_id');
    }

    public function loanNotifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadLoanNotifications()
    {
        return $this->loanNotifications()->whereNull('read_at');
    }
}
