<?php

namespace App\Http\Controllers;

use App\Enums\LoanStatus;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\Member;
use App\Support\CsvExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reports (spec §45). Every index method accepts the same filter shape
 * (date range / status / member / book / category) and, when the request
 * has reports.export permission and ?export=csv, streams a CSV instead of
 * rendering the view.
 */
class ReportController extends Controller
{
    public function loans(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $query = Loan::query()->with(['member', 'items.book'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('requested_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('requested_at', '<=', $request->date('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('member'), fn ($q) => $q->where('member_id', $request->input('member')))
            ->latest('requested_at');

        if ($request->query('export') === 'csv' && $request->user()->can('reports.export')) {
            return CsvExport::stream('laporan-peminjaman.csv', ['Kode', 'Anggota', 'Buku', 'Status', 'Diajukan', 'Jatuh Tempo'], $query->get()->flatMap(
                fn (Loan $loan) => $loan->items->map(fn ($item) => [
                    $loan->code, $loan->member->name, $item->book->title, $loan->status->label(),
                    $loan->requested_at->format('Y-m-d H:i'), $loan->due_at?->format('Y-m-d H:i'),
                ])
            ));
        }

        return view('reports.loans', [
            'loans' => $query->paginate(20)->withQueryString(),
            'statuses' => LoanStatus::cases(),
            'members' => Member::orderBy('name')->get(),
        ]);
    }

    public function returns(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $query = DB::table('loan_items')
            ->join('loans', 'loans.id', '=', 'loan_items.loan_id')
            ->join('books', 'books.id', '=', 'loan_items.book_id')
            ->join('members', 'members.id', '=', 'loans.member_id')
            ->where('loan_items.status', LoanStatus::RETURNED->value)
            ->when($request->filled('from'), fn ($q) => $q->whereDate('loan_items.returned_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('loan_items.returned_at', '<=', $request->date('to')))
            ->select('loans.code', 'members.name as member_name', 'books.title', 'loan_items.condition_on_return', 'loan_items.returned_at')
            ->orderByDesc('loan_items.returned_at');

        if ($request->query('export') === 'csv' && $request->user()->can('reports.export')) {
            return CsvExport::stream('laporan-pengembalian.csv', ['Kode', 'Anggota', 'Buku', 'Kondisi', 'Dikembalikan'], $query->get()->map(
                fn ($r) => [$r->code, $r->member_name, $r->title, $r->condition_on_return, $r->returned_at]
            ));
        }

        return view('reports.returns', ['rows' => $query->paginate(20)->withQueryString()]);
    }

    public function overdue(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        $query = Loan::where('status', LoanStatus::OVERDUE)->with(['member', 'items.book'])->oldest('due_at');

        if ($request->query('export') === 'csv' && $request->user()->can('reports.export')) {
            return CsvExport::stream('laporan-keterlambatan.csv', ['Kode', 'Anggota', 'Buku', 'Jatuh Tempo', 'Terlambat (hari)'], $query->get()->flatMap(
                fn (Loan $loan) => $loan->items->map(fn ($item) => [
                    $loan->code, $loan->member->name, $item->book->title,
                    $loan->due_at?->format('Y-m-d'), $loan->due_at ? $loan->due_at->diffInDays(now()) : null,
                ])
            ));
        }

        return view('reports.overdue', ['loans' => $query->paginate(20)->withQueryString()]);
    }

    public function statistics(Request $request)
    {
        abort_unless($request->user()->can('reports.view'), 403);

        // Driver-portable month bucketing (MySQL/MariaDB in dev & prod, SQLite in tests).
        $monthExpr = match (DB::getDriverName()) {
            'sqlite' => "strftime('%Y-%m', requested_at)",
            'pgsql' => "to_char(requested_at, 'YYYY-MM')",
            default => "DATE_FORMAT(requested_at, '%Y-%m')",
        };

        $loansByMonth = Loan::selectRaw("{$monthExpr} as ym, count(*) as total")
            ->where('requested_at', '>=', now()->subMonths(5)->startOfMonth())
            ->groupBy('ym')->orderBy('ym')->pluck('total', 'ym');

        $months = collect(range(5, 0))->map(fn ($i) => now()->copy()->subMonthsNoOverflow($i));

        $categoryStats = DB::table('loan_items')
            ->join('books', 'books.id', '=', 'loan_items.book_id')
            ->join('categories', 'categories.id', '=', 'books.category_id')
            ->select('categories.name', DB::raw('count(*) as total'))
            ->groupBy('categories.name')->orderByDesc('total')->get();

        $conditionStats = BookCopy::select('condition', DB::raw('count(*) as total'))
            ->groupBy('condition')->get();

        $activeMembers = Member::withCount('loans')->orderByDesc('loans_count')->take(10)->get();

        return view('reports.statistics', [
            'chart' => [
                'labels' => $months->map(fn ($m) => $m->translatedFormat('M Y'))->all(),
                'data' => $months->map(fn ($m) => (int) ($loansByMonth[$m->format('Y-m')] ?? 0))->all(),
            ],
            'categoryStats' => $categoryStats,
            'conditionStats' => $conditionStats,
            'activeMembers' => $activeMembers,
        ]);
    }
}
