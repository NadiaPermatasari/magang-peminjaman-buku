<?php

namespace App\Http\Controllers;

use App\Enums\FineStatus;
use App\Enums\LoanStatus;
use App\Models\ActivityLog;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Role-specific widgets (spec §42 anggota, §43 petugas, §44 super-admin).
 * Every query here is scoped/aggregated — no per-row authorization is
 * needed since nothing renders another member's individual records.
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
            'staffWidgets' => null,
            'adminWidgets' => null,
        ];

        if ($user->can('loans.view-own') && ! $user->can('loans.view-all')) {
            $data['member'] = $user->member;
            $data['memberWidgets'] = $data['member'] ? $this->memberWidgets($data['member']) : null;
        }

        if ($user->can('loans.approve') || $user->can('returns.process')) {
            $data['staffWidgets'] = $this->staffWidgets();
        }

        if ($user->can('reports.view') && $user->can('users.view')) {
            $data['adminWidgets'] = $this->adminWidgets();
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
            'unpaidFineTotal' => $member->fines()->where('status', FineStatus::UNPAID)->sum('amount'),
            'activeLoans' => $member->loans()->whereIn('status', $activeStatuses)
                ->with('items.book')->latest()->take(5)->get(),
            'recentHistory' => $member->loans()->whereNotIn('status', $activeStatuses)
                ->with('items.book')->latest()->take(5)->get(),
        ];
    }

    private function staffWidgets(): array
    {
        $today = now()->startOfDay();

        return [
            'pendingCount' => Loan::where('status', LoanStatus::PENDING)->count(),
            'readyCount' => Loan::where('status', LoanStatus::APPROVED)->count(),
            'dueTodayCount' => Loan::where('status', LoanStatus::BORROWED)
                ->whereBetween('due_at', [$today, $today->copy()->endOfDay()])->count(),
            'overdueCount' => Loan::where('status', LoanStatus::OVERDUE)->count(),
            'returnedTodayCount' => Loan::where('status', LoanStatus::RETURNED)
                ->where('returned_at', '>=', $today)->count(),
            'unpaidFineCount' => Fine::where('status', FineStatus::UNPAID)->count(),
        ];
    }

    private function adminWidgets(): array
    {
        $popularCategories = DB::table('loan_items')
            ->join('books', 'books.id', '=', 'loan_items.book_id')
            ->join('categories', 'categories.id', '=', 'books.category_id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        $popularBooks = DB::table('loan_items')
            ->join('books', 'books.id', '=', 'loan_items.book_id')
            ->select('books.title', DB::raw('count(*) as total'))
            ->groupBy('books.title')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return [
            'totalTitles' => Book::count(),
            'totalCopies' => BookCopy::count(),
            'totalMembers' => Member::count(),
            'activeLoans' => Loan::whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])->count(),
            'pendingLoans' => Loan::where('status', LoanStatus::PENDING)->count(),
            'overdueLoans' => Loan::where('status', LoanStatus::OVERDUE)->count(),
            'returnedThisMonth' => Loan::where('status', LoanStatus::RETURNED)
                ->where('returned_at', '>=', now()->startOfMonth())->count(),
            'unpaidFineTotal' => Fine::where('status', FineStatus::UNPAID)->sum('amount'),
            'popularCategories' => $popularCategories,
            'popularBooks' => $popularBooks,
        ];
    }
}
