<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Http\Requests\MemberAccountRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Member::class);

        $search = trim((string) $request->query('search', ''));
        $status = $request->query('status');

        $members = Member::query()
            ->with('user')
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('member_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('members.index', [
            'members' => $members,
            'statuses' => MemberStatus::cases(),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Member::class);

        return view('members.create', [
            'member' => new Member(['status' => MemberStatus::ACTIVE, 'joined_at' => now()]),
            'statuses' => MemberStatus::cases(),
        ]);
    }

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        if ($request->filled('identity_number') && Member::findByIdentityNumber($request->input('identity_number'))) {
            return back()->withInput()->withErrors(['identity_number' => 'Nomor identitas ini sudah terdaftar pada anggota lain.']);
        }

        $member = DB::transaction(function () use ($request) {
            $member = new Member($request->safe()->except(['identity_number', 'create_login', 'password']));
            $member->member_number = $this->nextMemberNumber();
            $member->setIdentityNumber($request->input('identity_number'));

            if ($request->boolean('create_login')) {
                // Kata sandi diatur petugas dan email langsung ditandai
                // terverifikasi, supaya anggota bisa login tanpa menunggu
                // email verifikasi/reset (server email opsional di sini).
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole('anggota');

                $member->user_id = $user->id;
            }

            $member->save();

            return $member;
        });

        Activity::log('MEMBER_CREATED', "Created member {$member->name} ({$member->member_number})", $member);

        $message = "Anggota {$member->name} berhasil ditambahkan.";

        if ($member->user_id) {
            Activity::log('MEMBER_ACCOUNT_CREATED', "Login account created for member {$member->member_number}", $member);
            $message .= " Akun login dibuat untuk {$member->email} — serahkan kata sandinya ke anggota.";
        }

        return redirect()->route('members.index')->with('success', $message);
    }

    public function edit(Member $member)
    {
        $this->authorize('update', $member);

        return view('members.edit', [
            'member' => $member->load('user'),
            'statuses' => MemberStatus::cases(),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member): RedirectResponse
    {
        if ($request->filled('identity_number')) {
            $existing = Member::findByIdentityNumber($request->input('identity_number'));
            if ($existing && $existing->id !== $member->id) {
                return back()->withInput()->withErrors(['identity_number' => 'Nomor identitas ini sudah terdaftar pada anggota lain.']);
            }
        }

        $member->fill($request->safe()->except('identity_number'));
        $member->setIdentityNumber($request->input('identity_number'));
        $member->save();

        Activity::log('MEMBER_UPDATED', "Updated member {$member->name} ({$member->member_number})", $member);

        return redirect()->route('members.index')->with('success', "Anggota {$member->name} berhasil diperbarui.");
    }

    /**
     * Membuat akun login untuk anggota yang belum punya, atau mengatur ulang
     * kata sandinya. Kata sandi tidak pernah masuk ke audit log (spec §24).
     */
    public function account(MemberAccountRequest $request, Member $member): RedirectResponse
    {
        $member->loadMissing('user');

        if ($member->user) {
            $member->user->forceFill(['password' => Hash::make($request->input('password'))])->save();

            Activity::log('MEMBER_PASSWORD_RESET', "Password reset for member {$member->member_number}", $member);

            return back()->with('success', "Kata sandi akun {$member->user->email} berhasil diperbarui.");
        }

        DB::transaction(function () use ($request, $member) {
            $user = User::create([
                'name' => $member->name,
                'email' => $member->email,
                'password' => Hash::make($request->input('password')),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $user->assignRole('anggota');

            $member->user_id = $user->id;
            $member->save();
        });

        Activity::log('MEMBER_ACCOUNT_CREATED', "Login account created for member {$member->member_number}", $member);

        return back()->with('success', "Akun login untuk {$member->email} berhasil dibuat — serahkan kata sandinya ke anggota.");
    }

    public function destroy(Member $member): RedirectResponse
    {
        $this->authorize('delete', $member);

        Activity::log('MEMBER_DELETED', "Deleted member {$member->name} ({$member->member_number})");

        $member->delete();

        return redirect()->route('members.index')->with('success', "Anggota {$member->name} berhasil dihapus.");
    }

    private function nextMemberNumber(): string
    {
        do {
            $candidate = 'M-'.now()->format('y').str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Member::withTrashed()->where('member_number', $candidate)->exists());

        return $candidate;
    }
}
