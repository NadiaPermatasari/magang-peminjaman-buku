<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CancelLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\HandoverLoan;
use App\Actions\Loans\RejectLoan;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Http\Requests\HandoverLoanRequest;
use App\Http\Requests\RejectLoanRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Models\Book;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /** "Pengajuan" — the signed-in member's own loan requests (spec §41). */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $member = $request->user()->member;

        abort_unless($member, 403, 'Akun Anda tidak terhubung ke data anggota.');

        $loans = $member->loans()
            ->with('items.book')
            ->latest()
            ->paginate((int) setting('per_page', 10));

        return view('loans.index', ['loans' => $loans]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Loan::class);

        abort_unless($request->user()->member, 403, 'Akun Anda tidak terhubung ke data anggota.');

        return view('loans.create', [
            'books' => Book::where('is_active', true)
                ->withCount(['copies as available_copies_count' => fn ($q) => $q->where('status', BookCopyStatus::AVAILABLE)])
                ->orderBy('title')
                ->get(),
            'preselected' => $request->query('book'),
        ]);
    }

    public function store(StoreLoanRequest $request, CreateLoan $action): RedirectResponse
    {
        $member = $request->user()->member;

        try {
            $loan = $action->handle($member, $request->input('book_ids'), $request->input('notes'));
        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('loans.show', $loan)->with('success', "Pengajuan {$loan->code} berhasil dikirim.");
    }

    public function show(Loan $loan)
    {
        $this->authorize('view', $loan);

        return view('loans.show', ['loan' => $loan->load(['items.book', 'items.bookCopy', 'items.fine', 'member', 'approvedBy', 'rejectedBy'])]);
    }

    /** "Menunggu Verifikasi" — staff queue. */
    public function pending(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::where('status', LoanStatus::PENDING)
            ->with(['member', 'items.book'])
            ->oldest('requested_at')
            ->paginate((int) setting('per_page', 10));

        return view('loans.pending', ['loans' => $loans]);
    }

    public function approve(Loan $loan, ApproveLoan $action): RedirectResponse
    {
        $this->authorize('approve', $loan);

        try {
            $action->handle($loan, auth()->user());
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Peminjaman {$loan->code} disetujui.");
    }

    public function reject(RejectLoanRequest $request, Loan $loan, RejectLoan $action): RedirectResponse
    {
        try {
            $action->handle($loan, auth()->user(), $request->string('reason'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Peminjaman {$loan->code} ditolak.");
    }

    /** "Siap Diambil" — staff handover-by-barcode queue. */
    public function ready(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::where('status', LoanStatus::APPROVED)
            ->with(['member', 'items.book', 'items.bookCopy'])
            ->oldest('approved_at')
            ->paginate((int) setting('per_page', 10));

        return view('loans.ready', ['loans' => $loans]);
    }

    public function handover(HandoverLoanRequest $request, Loan $loan, HandoverLoan $action): RedirectResponse
    {
        try {
            $action->handle($loan, $request->string('barcode'), auth()->user());
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Eksemplar berhasil diserahkan.');
    }

    /** "Peminjaman Aktif" — staff view of everything currently borrowed. */
    public function active(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])
            ->with(['member', 'items.book', 'items.bookCopy'])
            ->oldest('due_at')
            ->paginate((int) setting('per_page', 10));

        return view('loans.active', ['loans' => $loans]);
    }

    /** "Keterlambatan" — staff view. */
    public function overdue(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $loans = Loan::where('status', LoanStatus::OVERDUE)
            ->with(['member', 'items.book', 'items.bookCopy'])
            ->oldest('due_at')
            ->paginate((int) setting('per_page', 10));

        return view('loans.overdue', ['loans' => $loans]);
    }

    public function cancel(Loan $loan, CancelLoan $action): RedirectResponse
    {
        $this->authorize('cancel', $loan);

        try {
            $action->handle($loan, auth()->user());
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Peminjaman {$loan->code} dibatalkan.");
    }
}
