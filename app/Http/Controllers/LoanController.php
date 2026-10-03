<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ApproveLoan;
use App\Actions\Loans\CancelLoan;
use App\Actions\Loans\CreateLoan;
use App\Actions\Loans\HandoverLoan;
use App\Actions\Loans\RejectLoan;
use App\Enums\BookCopyStatus;
use App\Enums\LoanStatus;
use App\Enums\MemberStatus;
use App\Exceptions\LoanException;
use App\Http\Requests\HandoverLoanRequest;
use App\Http\Requests\RejectLoanRequest;
use App\Http\Requests\StoreLoanRequest;
use App\Http\Requests\UploadHandoverPhotoRequest;
use App\Models\Book;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\Member;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LoanController extends Controller
{
    /**
     * "Pengajuan" (spec §41). Anggota melihat pengajuannya sendiri; petugas
     * dan admin (loans.view-all) melihat seluruh pengajuan. Akun staf tidak
     * terhubung ke data anggota, jadi halaman ini tidak boleh menolak mereka
     * hanya karena itu.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Loan::class);

        $user = $request->user();
        $seesAll = $user->can('loans.view-all');
        $member = $user->member;

        if (! $seesAll && ! $member) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun Anda tidak terhubung ke data anggota, sehingga belum bisa mengajukan peminjaman. Hubungi petugas perpustakaan.');
        }

        $status = $request->query('status');

        $loans = Loan::query()
            ->when(! $seesAll, fn ($q) => $q->where('member_id', $member->id))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['member', 'items.book'])
            ->latest('requested_at')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('loans.index', [
            'loans' => $loans,
            'seesAll' => $seesAll,
            'statuses' => LoanStatus::cases(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Loan::class);

        $member = $request->user()->member;
        $onBehalf = ! $member && $request->user()->can('loans.view-all');

        if (! $member && ! $onBehalf) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun Anda tidak terhubung ke data anggota, sehingga belum bisa mengajukan peminjaman. Hubungi petugas perpustakaan.');
        }

        return view('loans.create', [
            'books' => Book::where('is_active', true)
                ->withCount(['copies as available_copies_count' => fn ($q) => $q->where('status', BookCopyStatus::AVAILABLE)])
                ->orderBy('title')
                ->get(),
            'preselected' => $request->query('book'),
            // Petugas loket mengajukan atas nama anggota; anggota sendiri
            // tidak pernah melihat pilihan ini.
            'onBehalf' => $onBehalf,
            'members' => $onBehalf
                ? Member::where('status', MemberStatus::ACTIVE)->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function store(StoreLoanRequest $request, CreateLoan $action): RedirectResponse
    {
        $member = $request->user()->member;

        if (! $member) {
            // Pengajuan atas nama anggota — hanya untuk staf (loans.view-all),
            // dipaksakan di StoreLoanRequest lewat aturan member_id.
            abort_unless($request->user()->can('loans.view-all'), 403);

            $member = Member::findOrFail($request->integer('member_id'));
        }

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

        return view('loans.show', [
            'loan' => $loan->load([
                'items.book', 'items.bookCopy', 'member', 'approvedBy', 'rejectedBy',
                'extensions.requestedBy', 'extensions.decidedBy',
            ]),
        ]);
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

    /** "Siap Diambil" — antrean serah terima buku ke anggota. */
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
        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('loan-proofs', 'public')
            : null;

        try {
            $action->handle($loan, $request->string('barcode'), auth()->user(), $photoPath);
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Eksemplar berhasil diserahkan.');
    }

    /**
     * Unggah / perbarui bukti foto serah terima dari halaman Peminjaman
     * Aktif, untuk item yang sudah diserahkan tanpa foto.
     */
    public function uploadHandoverPhoto(UploadHandoverPhotoRequest $request, Loan $loan, LoanItem $item): RedirectResponse
    {
        abort_unless($item->loan_id === $loan->id, 404);

        // Bukti lama diganti, bukan ditumpuk — hapus filenya agar tidak jadi
        // sampah di storage (pola yang sama dipakai untuk sampul buku).
        if ($item->handover_photo_path) {
            Storage::disk('public')->delete($item->handover_photo_path);
        }

        $item->update([
            'handover_photo_path' => $request->file('photo')->store('loan-proofs', 'public'),
        ]);

        Activity::log('LOAN_HANDOVER_PHOTO_UPLOADED', "Handover proof uploaded for loan {$loan->code}", $loan);

        return back()->with('success', 'Bukti foto berhasil disimpan.');
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
