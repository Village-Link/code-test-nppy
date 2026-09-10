<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = [
            'dashboard.view-admin',
            'loans.view-own',
            'loans.view-all',
            'loans.view-assigned',
            'loans.create',
            'loans.cancel',
            'loans.assign-reviewer',
            'loans.review',
            'loans.approve',
            'loans.reject',
            'loans.disburse',
            'loans.close',
            'repayments.view-own',
            'repayments.pay-own',
            'repayments.view-all',
            'repayments.record',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'admin' => $permissions,
            'loan_officer' => [
                'loans.view-assigned',
                'loans.review',
                'repayments.view-all',
                'repayments.record',
            ],
            'customer' => [
                'loans.view-own',
                'loans.create',
                'loans.cancel',
                'repayments.view-own',
                'repayments.pay-own',
            ],
        ];
        foreach ($roles as $name => $rolePermissions) {
            Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ])->syncPermissions($rolePermissions);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
