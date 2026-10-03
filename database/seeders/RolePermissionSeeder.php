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
        'racks.view', 'racks.create', 'racks.update', 'racks.delete',

        'members.view', 'members.create', 'members.update', 'members.delete',

        'loans.create', 'loans.view-own', 'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover', 'loans.cancel',

        'loan-extensions.create', 'loan-extensions.view', 'loan-extensions.approve',

        'returns.process',

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
            'racks.view', 'racks.create', 'racks.update', 'racks.delete',
            'members.view', 'members.create', 'members.update', 'members.delete',
            // loans.create: petugas loket boleh mengajukan atas nama anggota
            // (anggota memilih sendiri dari akunnya, staf dari halaman Pengajuan).
            'loans.create', 'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover', 'loans.cancel',
            'loan-extensions.view', 'loan-extensions.approve',
            'returns.process',
            'reports.view', 'reports.export',
        ],

        'petugas' => [
            'dashboard.view',
            'books.view', 'book-copies.view', 'categories.view', 'racks.view',
            'members.view',
            'loans.create', 'loans.view-all', 'loans.approve', 'loans.reject', 'loans.handover',
            'loan-extensions.view', 'loan-extensions.approve',
            'returns.process',
        ],

        'anggota' => [
            'dashboard.view',
            'books.view',
            'loans.create', 'loans.view-own', 'loans.cancel',
            'loan-extensions.create',
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

        // Katalog permission bersifat code-defined: permission yang sudah
        // tidak ada di sini (mis. sisa modul yang dihapus) ikut dibuang
        // beserta penugasannya ke role, supaya halaman Role/Permission
        // tidak menampilkan hak akses yang tidak punya layar lagi.
        Permission::where('guard_name', 'web')->whereNotIn('name', self::PERMISSIONS)->delete();

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(self::PERMISSIONS);

        foreach (self::ROLE_PERMISSIONS as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions);
        }

        Cache::forget('spatie.permission.cache');
    }
}
