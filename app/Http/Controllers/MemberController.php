<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use App\Models\User;
use App\Support\Activity;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
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
            $member = new Member($request->safe()->except(['identity_number', 'create_login']));
            $member->member_number = $this->nextMemberNumber();
            $member->setIdentityNumber($request->input('identity_number'));

            if ($request->boolean('create_login')) {
                $user = User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => Hash::make(Str::random(40)),
                ]);
                $user->assignRole('anggota');
                $member->user_id = $user->id;
                event(new Registered($user));
                Password::sendResetLink(['email' => $user->email]);
            }

            $member->save();

            return $member;
        });

        Activity::log('MEMBER_CREATED', "Created member {$member->name} ({$member->member_number})", $member);

        return redirect()->route('members.index')->with('success', "Anggota {$member->name} berhasil ditambahkan.");
    }

    public function edit(Member $member)
    {
        $this->authorize('update', $member);

        return view('members.edit', [
            'member' => $member,
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
