<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Creates the permission catalogue (spec §4) and the 5 default roles with
 * their assignments (spec §5). Idempotent — safe to re-run.
 */
class RolePermissionSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private const PERMISSIONS = [
        'dashboard.view',

        'books.view', 'books.create', 'books.update', 'books.delete',
        'book-copies.view', 'book-copies.create', 'book-copies.update', 'book-copies.delete',
        'categories.view', 'categories.create', 'categories.update', 'categories.delete',
        'authors.view', 'authors.create', 'authors.update', 'authors.delete',
        'publishers.view', 'publishers.create', 'publishers.update', 'publishers.delete',
        'racks.view', 'racks.create', 'racks.update', 'racks.delete',

        'members.view', 'members.create', 'members.update', 'members.delete',

        'loans.create', 'loans.view-own', 'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover', 'loans.cancel',

        'returns.process',

        'fines.view-own', 'fines.view', 'fines.mark-paid', 'fines.waive',

        'reports.view', 'reports.export',

        'users.view', 'users.create', 'users.update', 'users.disable',

        'roles.manage', 'permissions.manage',

        'settings.manage',

        'audit-logs.view',

        'security-dashboard.view',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const ROLE_PERMISSIONS = [
        'admin-perpustakaan' => [
            'dashboard.view',
            'books.view', 'books.create', 'books.update', 'books.delete',
            'book-copies.view', 'book-copies.create', 'book-copies.update', 'book-copies.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'authors.view', 'authors.create', 'authors.update', 'authors.delete',
            'publishers.view', 'publishers.create', 'publishers.update', 'publishers.delete',
            'racks.view', 'racks.create', 'racks.update', 'racks.delete',
            'members.view', 'members.create', 'members.update', 'members.delete',
            'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover', 'loans.cancel',
            'returns.process',
            'fines.view', 'fines.mark-paid',
            'reports.view', 'reports.export',
        ],

        'petugas' => [
            'dashboard.view',
            'books.view', 'book-copies.view', 'categories.view', 'authors.view', 'publishers.view', 'racks.view',
            'members.view',
            'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover',
            'returns.process',
            'fines.view', 'fines.mark-paid',
        ],

        'anggota' => [
            'dashboard.view',
            'books.view',
            'loans.create', 'loans.view-own', 'loans.cancel',
            'fines.view-own',
        ],

        'pimpinan' => [
            'dashboard.view',
            'reports.view',
        ],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        Cache::forget('spatie.permission.cache');
    }
}
