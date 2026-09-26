<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AppNotification;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

/**
 * Staff/admin account management. Members (anggota) are managed from the
 * Members module; this screen is for accounts that sign in to the back
 * office (spec §41 Administrasi > User). Public self-registration is
 * disabled (spec §58) — every account is provisioned here.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = trim((string) $request->query('search', ''));
        $roleFilter = $request->query('role');

        $users = User::query()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, fn ($q) => $q->role($roleFilter))
            ->latest()
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('users.create', [
            'user' => new User,
            'roles' => $this->assignableRoles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $this->validated($request);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->syncRoles([$data['role']]);

        if ($request->boolean('verified')) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        Activity::log('USER_CREATED', "Created user {$user->name}", $user);
        Activity::log('ROLE_ASSIGNED', "Assigned role {$data['role']} to {$user->name}", $user);

        $user->notify(new AppNotification(
            'Akun Anda telah dibuat',
            'Administrator membuat akun ini untuk Anda. Selamat datang di '.app_name().'!',
            'ni ni-single-02',
            route('profile'),
            'from-emerald-500 to-teal-400',
        ));

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'activities' => $user->activities()->take(5)->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $this->validated($request, $user);
        $previousRole = $user->getRoleNames()->first();

        if ($previousRole !== $data['role'] && $user->id === $request->user()->id) {
            return back()->withInput()->with('error', 'Anda tidak dapat mengubah role Anda sendiri.');
        }

        if ($previousRole === 'super-admin' && $data['role'] !== 'super-admin' && User::role('super-admin')->count() === 1) {
            return back()->withInput()->with('error', 'Ini adalah satu-satunya akun super-admin; tetapkan super-admin lain terlebih dahulu.');
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->forceFill([
            'email_verified_at' => $request->boolean('verified') ? ($user->email_verified_at ?? now()) : null,
        ])->save();

        Activity::log('USER_UPDATED', "Updated user {$user->name}", $user);

        if ($previousRole !== $data['role']) {
            $user->syncRoles([$data['role']]);
            Activity::log('ROLE_ASSIGNED', "Changed role of {$user->name} from {$previousRole} to {$data['role']}", $user, values: [
                'before' => ['role' => $previousRole],
                'after' => ['role' => $data['role']],
            ]);
        }

        if ($user->id !== $request->user()->id) {
            $user->notify(new AppNotification(
                'Akun Anda telah diperbarui',
                'Administrator memperbarui detail akun Anda.',
                'ni ni-settings',
                route('profile'),
            ));
        }

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} berhasil diperbarui.");
    }

    /**
     * "Disable" a user (spec §4 permission is users.disable, not delete —
     * this is a soft delete: the account can no longer sign in and is
     * hidden from lists, but its history is preserved and it can be
     * restored). No hard delete is exposed for user accounts.
     */
    public function disable(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri di sini.');
        }

        if ($user->hasRole('super-admin') && User::role('super-admin')->count() === 1) {
            return back()->with('error', 'Ini adalah satu-satunya akun super-admin; tetapkan super-admin lain terlebih dahulu.');
        }

        Activity::log('USER_DISABLED', "Disabled user {$user->name} ({$user->email})");

        $user->delete();

        return redirect()->route('users.index')->with('success', "Pengguna {$user->name} dinonaktifkan.");
    }

    /**
     * @return array<string, string>
     */
    protected function assignableRoles(): array
    {
        $names = Role::query()->where('name', '!=', 'anggota')->orderBy('name')->pluck('name');

        if (! auth()->user()->can('roles.manage')) {
            $names = $names->reject(fn ($name) => $name === 'super-admin');
        }

        return $names->mapWithKeys(fn ($name) => [$name => str_replace('-', ' ', ucfirst($name))])->all();
    }

    /**
     * Validation rules shared by store / update.
     *
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?User $user = null): array
    {
        $passwordRules = $user
            ? ['nullable', 'confirmed', Password::defaults()]
            : ['required', 'confirmed', Password::defaults()];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'role' => ['required', Rule::in(array_keys($this->assignableRoles()))],
            'password' => $passwordRules,
        ]);
    }
}
