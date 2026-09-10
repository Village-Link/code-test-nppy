<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
            ],
            [
                'name' => 'Loan Officer',
                'email' => 'officer@example.com',
                'role' => 'loan_officer',
            ],
            [
                'name' => 'Sample Customer',
                'email' => 'customer@example.com',
                'role' => 'customer',
                'phone' => '09123456789',
                'address' => 'Yangon',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('P@ssw0rd'),
                ]
            );

            $user->syncRoles([$data['role']]);

            if ($data['role'] === 'customer') {
                $user->customer()->updateOrCreate([], [
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                ]);
            }
        }
    }
}
