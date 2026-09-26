<?php

namespace App\Http\Controllers;

use App\Actions\Fines\MarkFinePaid;
use App\Actions\Fines\WaiveFine;
use App\Enums\FineStatus;
use App\Exceptions\LoanException;
use App\Http\Requests\MarkFinePaidRequest;
use App\Http\Requests\WaiveFineRequest;
use App\Models\Fine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Fine::class);

        $user = $request->user();
        $status = $request->query('status');

        $query = Fine::with(['member', 'loanItem.book']);

        if (! $user->can('fines.view')) {
            $member = $user->member;
            abort_unless($member, 403, 'Akun Anda tidak terhubung ke data anggota.');
            $query->where('member_id', $member->id);
        }

        $fines = $query
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest('calculated_at')
            ->paginate((int) setting('per_page', 10))
            ->withQueryString();

        return view('fines.index', [
            'fines' => $fines,
            'statuses' => FineStatus::cases(),
        ]);
    }

    public function markPaid(MarkFinePaidRequest $request, Fine $fine, MarkFinePaid $action): RedirectResponse
    {
        try {
            $action->handle($fine, auth()->user(), $request->input('method'), $request->input('notes'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Denda ditandai lunas.');
    }

    public function waive(WaiveFineRequest $request, Fine $fine, WaiveFine $action): RedirectResponse
    {
        try {
            $action->handle($fine, auth()->user(), $request->string('reason'));
        } catch (LoanException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Denda dibebaskan.');
    }
}
