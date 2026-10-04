<?php

namespace App\Http\Controllers;

use App\Enums\ExtensionStatus;
use App\Enums\LoanStatus;
use App\Models\ActivityLog;
use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * Role-specific widgets (spec §42 anggota, §43 petugas, §44 super-admin).
 * Every query here is scoped/aggregated — no per-row authorization is
 * needed since nothing renders another member's individual records.
 *
 * Kartu petugas/admin disatukan dalam satu daftar (tidak lagi dipisah
 * staff/admin) supaya metrik yang sama — mis. perpanjangan menunggu —
 * hanya muncul sekali untuk super-admin yang memegang kedua izin.
 */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $data = [
            'unreadNotifications' => $user->unreadNotifications()->count(),
            'recentActivities' => $user->can('audit-logs.view')
                ? ActivityLog::with('user')->latest()->take(8)->get()
                : $user->activities()->take(8)->get(),
            'member' => null,
            'memberWidgets' => null,
            'staffCards' => $this->staffCards($user),
        ];

        if ($user->can('loans.view-own') && ! $user->can('loans.view-all')) {
            $data['member'] = $user->member;
            $data['memberWidgets'] = $data['member'] ? $this->memberWidgets($data['member']) : null;
        }

        return view('pages.dashboard', $data);
    }

    private function memberWidgets(Member $member): array
    {
        $activeStatuses = [LoanStatus::APPROVED, LoanStatus::BORROWED, LoanStatus::OVERDUE];

        return [
            'borrowedCount' => $member->loans()->where('status', LoanStatus::BORROWED)->count(),
            'pendingCount' => $member->loans()->where('status', LoanStatus::PENDING)->count(),
            'dueSoonCount' => $member->loans()->where('status', LoanStatus::BORROWED)
                ->whereBetween('due_at', [now(), now()->addDays(3)])->count(),
            'overdueCount' => $member->loans()->where('status', LoanStatus::OVERDUE)->count(),
            'pendingExtensionCount' => LoanExtension::where('status', ExtensionStatus::PENDING)
                ->whereHas('loan', fn ($q) => $q->where('member_id', $member->id))->count(),
            'activeLoans' => $member->loans()->whereIn('status', $activeStatuses)
                ->with('items.book')->latest()->take(5)->get(),
            'recentHistory' => $member->loans()->whereNotIn('status', $activeStatuses)
                ->with('items.book')->latest()->take(5)->get(),
        ];
    }

    /**
     * Kartu ringkasan petugas/admin. `value` sengaja berupa closure supaya
     * query hanya dijalankan untuk kartu yang memang boleh dilihat user ini.
     *
     * @return list<array{label: string, value: int, icon: string, gradient: string, url: string}>
     */
    private function staffCards(User $user): array
    {
        $today = now()->startOfDay();

        $definitions = [
            [
                'permission' => 'loans.approve',
                'route' => 'loans.pending',
                'label' => 'Menunggu Verifikasi',
                'icon' => 'ni ni-watch-time',
                'gradient' => 'from-orange-500 to-yellow-500',
                'value' => fn () => Loan::where('status', LoanStatus::PENDING)->count(),
            ],
            [
                'permission' => 'loans.view-all',
                'route' => 'loans.overdue',
                'label' => 'Terlambat',
                'icon' => 'ni ni-fat-remove',
                'gradient' => 'from-red-600 to-orange-600',
                'value' => fn () => Loan::where('status', LoanStatus::OVERDUE)->count(),
            ],
            [
                'permission' => 'loans.approve',
                'route' => 'loans.ready',
                'label' => 'Siap Diambil',
                'icon' => 'ni ni-box-2',
                'gradient' => 'from-cyan-500 to-blue-500',
                'value' => fn () => Loan::where('status', LoanStatus::APPROVED)->count(),
            ],
            [
                'permission' => 'returns.process',
                'route' => 'loans.index',
                'params' => ['status' => LoanStatus::RETURNED->value],
                'label' => 'Dikembalikan Hari Ini',
                'icon' => 'ni ni-check-bold',
                'gradient' => 'from-emerald-500 to-teal-400',
                'value' => fn () => Loan::where('status', LoanStatus::RETURNED)
                    ->where('returned_at', '>=', $today)->count(),
            ],
            [
                'permission' => 'loans.view-all',
                'route' => 'loans.active',
                'label' => 'Jatuh Tempo Hari Ini',
                'icon' => 'ni ni-time-alarm',
                'gradient' => 'from-blue-500 to-violet-500',
                'value' => fn () => Loan::where('status', LoanStatus::BORROWED)
                    ->whereBetween('due_at', [$today, $today->copy()->endOfDay()])->count(),
            ],
            [
                'permission' => 'loan-extensions.view',
                'route' => 'loan-extensions.index',
                'label' => 'Perpanjangan Menunggu',
                'icon' => 'ni ni-calendar-grid-58',
                'gradient' => 'from-slate-700 to-slate-500',
                'value' => fn () => LoanExtension::where('status', ExtensionStatus::PENDING)->count(),
            ],
            [
                'permission' => 'members.view',
                'route' => 'members.index',
                'label' => 'Total Anggota',
                'icon' => 'ni ni-circle-08',
                'gradient' => 'from-emerald-500 to-teal-400',
                'value' => fn () => Member::count(),
            ],
            [
                'permission' => 'loans.view-all',
                'route' => 'loans.active',
                'label' => 'Peminjaman Aktif',
                'icon' => 'ni ni-cart',
                'gradient' => 'from-orange-500 to-yellow-500',
                'value' => fn () => Loan::whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])->count(),
            ],
            [
                'permission' => 'loans.view-all',
                'route' => 'loans.index',
                'params' => ['status' => LoanStatus::PENDING->value],
                'label' => 'Pengajuan Pending',
                'icon' => 'ni ni-single-copy-04',
                'gradient' => 'from-orange-500 to-yellow-500',
                'value' => fn () => Loan::where('status', LoanStatus::PENDING)->count(),
            ],
        ];

        $cards = [];

        foreach ($definitions as $card) {
            if (! $user->can($card['permission']) || ! Route::has($card['route'])) {
                continue;
            }

            $cards[] = [
                'label' => $card['label'],
                'value' => ($card['value'])(),
                'icon' => $card['icon'],
                'gradient' => $card['gradient'],
                'url' => route($card['route'], $card['params'] ?? []),
            ];
        }

        return $cards;
    }
}
