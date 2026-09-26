<?php

namespace App\Http\Controllers;

use App\Actions\Loans\ReturnLoan;
use App\Enums\BookCondition;
use App\Exceptions\LoanException;
use App\Http\Requests\ReturnLoanRequest;
use Illuminate\Http\RedirectResponse;

class ReturnController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->can('returns.process'), 403);

        return view('returns.index', ['conditions' => BookCondition::cases()]);
    }

    public function store(ReturnLoanRequest $request, ReturnLoan $action): RedirectResponse
    {
        try {
            $result = $action->handle(
                $request->string('barcode'),
                BookCondition::from($request->string('condition')->toString()),
                auth()->user(),
                $request->input('notes'),
            );
        } catch (LoanException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = "Buku berhasil dikembalikan untuk peminjaman {$result['loan']->code}.";

        if ($result['fine']) {
            $amount = number_format($result['fine']->amount, 0, ',', '.');
            $message .= " Denda keterlambatan: Rp{$amount}.";
        }

        return back()->with('success', $message);
    }
}
