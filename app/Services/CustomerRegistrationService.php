<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class CustomerRegistrationService
{
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $user->assignRole('customer');

            $user->customer()->create([
                'phone' => $data['phone'],
                'address' => $data['address'],
            ]);

            return $user;
        });
    }
}
