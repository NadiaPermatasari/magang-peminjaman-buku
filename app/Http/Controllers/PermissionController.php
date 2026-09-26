<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Permission;

/**
 * Read-only permission reference (spec §41 Administrasi > Permission).
 * Permissions are a fixed, code-defined catalogue (RolePermissionSeeder) —
 * assigning them to a role happens on the Role screens, not here.
 */
class PermissionController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('permissions.manage'), 403);

        $groups = Permission::with('roles')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $p) => explode('.', $p->name)[0]);

        return view('permissions.index', ['groups' => $groups]);
    }
}
