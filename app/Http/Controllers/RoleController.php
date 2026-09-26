<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Role management (spec §41 Administrasi > Role), backed by
 * spatie/laravel-permission. Permissions themselves are a fixed, code-defined
 * catalogue (RolePermissionSeeder) — this screen assigns/revokes them per
 * role, it does not create ad-hoc permission strings (see PermissionController
 * for the read-only reference list).
 */
class RoleController extends Controller
{
    /** Seeded roles the system's own code depends on by name — never deletable. */
    private const PROTECTED_ROLES = ['super-admin', 'admin-perpustakaan', 'petugas', 'anggota', 'pimpinan'];

    public function index()
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);

        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return view('roles.index', [
            'roles' => $roles,
            'protectedRoles' => self::PROTECTED_ROLES,
        ]);
    }

    public function create()
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);

        return view('roles.create', [
            'role' => new Role,
            'permissionGroups' => $this->permissionGroups(),
            'selected' => [],
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->validated('name'), 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        Activity::log('PERMISSION_CHANGED', "Created role {$role->name}", $role);

        return redirect()->route('roles.index')->with('success', "Role {$role->name} berhasil dibuat.");
    }

    public function edit(Role $role)
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);

        return view('roles.edit', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
            'selected' => $role->permissions->pluck('name')->all(),
            'isProtected' => $role->name === 'super-admin',
        ]);
    }

    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super-admin') {
            return back()->with('error', 'Permission role super-admin tidak dapat diubah — role ini selalu memiliki seluruh permission.');
        }

        $before = $role->permissions->pluck('name')->sort()->values()->all();

        if ($role->name !== $request->validated('name') && in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->withInput()->with('error', 'Nama role bawaan sistem tidak dapat diubah.');
        }

        $role->update(['name' => $request->validated('name')]);
        $role->syncPermissions($request->input('permissions', []));

        $after = $role->permissions->pluck('name')->sort()->values()->all();

        Activity::log('PERMISSION_CHANGED', "Updated permissions for role {$role->name}", $role, values: [
            'before' => ['permissions' => $before],
            'after' => ['permissions' => $after],
        ]);

        return redirect()->route('roles.index')->with('success', "Role {$role->name} berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_unless(auth()->user()->can('roles.manage'), 403);

        if (in_array($role->name, self::PROTECTED_ROLES, true)) {
            return back()->with('error', 'Role bawaan sistem tidak dapat dihapus.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh pengguna.');
        }

        Activity::log('PERMISSION_CHANGED', "Deleted role {$role->name}");

        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role {$role->name} berhasil dihapus.");
    }

    /**
     * All permissions grouped by resource prefix (e.g. "books.create" -> "books").
     *
     * @return Collection<string, Collection>
     */
    private function permissionGroups()
    {
        return Permission::orderBy('name')->get()->groupBy(fn (Permission $p) => explode('.', $p->name)[0]);
    }
}
