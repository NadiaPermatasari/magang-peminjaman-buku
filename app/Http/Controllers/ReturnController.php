<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ReturnLoan;
use App\Enums\BookCondition;
use App\Enums\LoanStatus;
use App\Exceptions\LoanException;
use App\Http\Requests\ReturnLoanRequest;
use App\Models\LoanItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Pengembalian buku (spec §16). Petugas memilih eksemplar dari daftar
 * peminjaman yang sedang berjalan lalu mengunggah bukti foto — tidak ada
 * lagi input/scan barcode.
 */
class ReturnController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->can('returns.process'), 403);

        $search = trim((string) $request->query('search', ''));

        $items = LoanItem::query()
            ->whereIn('status', [LoanStatus::BORROWED, LoanStatus::OVERDUE])
            ->with(['book', 'bookCopy', 'loan.member'])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('book', fn ($b) => $b->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('loan', fn ($l) => $l->where('code', 'like', "%{$search}%"))
                    ->orWhereHas('loan.member', fn ($m) => $m->where('name', 'like', "%{$search}%")->orWhere('member_number', 'like', "%{$search}%"));
            })
            ->oldest('due_at')
            ->get();

        return view('returns.index', [
            'items' => $items,
            'conditions' => BookCondition::cases(),
            'search' => $search,
        ]);
    }

    public function store(ReturnLoanRequest $request, ReturnLoan $action): RedirectResponse
    {
        $item = LoanItem::findOrFail($request->integer('loan_item_id'));

        $photoPath = $request->file('photo')->store('loan-proofs', 'public');

        try {
            $result = $action->handle(
                $item,
                BookCondition::from($request->string('condition')->toString()),
                $request->user(),
                $photoPath,
                $request->input('notes'),
            );
        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $loan = $result['loan'];
        $message = "Buku berhasil dikembalikan untuk peminjaman {$loan->code}.";

        if ($loan->status === LoanStatus::RETURNED) {
            $message .= ' Seluruh eksemplar sudah kembali, peminjaman ditutup.';
        }

        return back()->with('success', $message);
    }
}
