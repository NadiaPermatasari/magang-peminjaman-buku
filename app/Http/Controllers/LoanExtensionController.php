<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ApproveLoanExtension;
use App\Actions\Loans\RejectLoanExtension;
use App\Actions\Loans\RequestLoanExtension;
use App\Enums\ExtensionStatus;
use App\Exceptions\LoanException;
use App\Http\Requests\DecideLoanExtensionRequest;
use App\Http\Requests\StoreLoanExtensionRequest;
use App\Models\Loan;
use App\Models\LoanExtension;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Perpanjangan / banding peminjaman: anggota mengajukan tambahan hari dari
 * halaman detail peminjaman, petugas atau admin menyetujui (jatuh tempo
 * digeser) atau menolaknya dengan alasan.
 */
class LoanExtensionController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', LoanExtension::class);

        $user = $request->user();
        $seesAll = $user->can('loan-extensions.view');
        $member = $user->member;

        if (! $seesAll && ! $member) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun Anda tidak terhubung ke data anggota.');
        }

        $status = $request->query('status');

        $extensions = LoanExtension::query()
            ->when(! $seesAll, fn ($q) => $q->whereHas('loan', fn ($loan) => $loan->where('member_id', $member->id)))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['loan.member', 'loan.items.book', 'requestedBy', 'decidedBy'])
            ->orderByRaw("CASE WHEN status = 'PENDING' THEN 0 ELSE 1 END")
            ->latest('requested_at')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('loan-extensions.index', [
            'extensions' => $extensions,
            'seesAll' => $seesAll,
            'statuses' => ExtensionStatus::cases(),
        ]);
    }

    public function store(StoreLoanExtensionRequest $request, Loan $loan, RequestLoanExtension $action): RedirectResponse
    {
        try {
            $action->handle($loan, $request->user(), $request->integer('days'), $request->string('reason')->toString());
        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan perpanjangan dikirim dan menunggu persetujuan petugas.');
    }

    public function approve(DecideLoanExtensionRequest $request, LoanExtension $extension, ApproveLoanExtension $action): RedirectResponse
    {
        try {
            $extension = $action->handle($extension, $request->user(), $request->input('note'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        $dueAt = $extension->new_due_at?->translatedFormat('d M Y H:i');

        return back()->with('success', "Perpanjangan disetujui. Jatuh tempo {$extension->loan->code} menjadi {$dueAt}.");
    }

    public function reject(DecideLoanExtensionRequest $request, LoanExtension $extension, RejectLoanExtension $action): RedirectResponse
    {
        try {
            $action->handle($extension, $request->user(), $request->string('note')->toString());
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Pengajuan perpanjangan ditolak.');
    }
}
