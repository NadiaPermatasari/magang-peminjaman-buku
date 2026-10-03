<?php

namespace App\Actions\Loans;

use App\Enums\ExtensionStatus;
use App\Exceptions\LoanException;
use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\User;
use App\Notifications\LoanExtensionSubmittedNotification;
use App\Notifications\NewLoanExtensionPendingNotification;
use App\Support\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Anggota mengajukan perpanjangan (banding) atas peminjaman yang sedang
 * berjalan. Pengajuan hanya dicatat sebagai PENDING — jatuh tempo baru
 * dihitung saat petugas/admin menyetujuinya (ApproveLoanExtension).
 */
class RequestLoanExtension
{
    public function handle(Loan $loan, User $requester, int $days, string $reason): LoanExtension
    {
        // Tanpa default eksplisit, supaya nilai dari Setting::$defaults yang
        // dipakai bila pengaturannya belum pernah disimpan.
        if (! setting('allow_renewal')) {
            throw new LoanException('Perpanjangan peminjaman sedang tidak diizinkan oleh pengaturan perpustakaan.');
        }

        if (! $loan->isActive()) {
            throw new LoanException("Peminjaman berstatus {$loan->status->label()} tidak dapat diperpanjang.");
        }

        $maxRenewals = (int) setting('max_renewals', 1);

        if ($maxRenewals > 0 && $loan->approvedExtensionCount() >= $maxRenewals) {
            throw new LoanException("Peminjaman ini sudah mencapai batas maksimum perpanjangan ({$maxRenewals}x).");
        }

        if ($maxRenewals === 0) {
            throw new LoanException('Perpanjangan peminjaman sedang tidak diizinkan oleh pengaturan perpustakaan.');
        }

        $extension = DB::transaction(function () use ($loan, $requester, $days, $reason) {
            // Cek di dalam transaksi supaya dua submit berbarengan tidak
            // menghasilkan dua pengajuan menunggu untuk peminjaman yang sama.
            $pending = $loan->extensions()
                ->where('status', ExtensionStatus::PENDING)
                ->lockForUpdate()
                ->exists();

            if ($pending) {
                throw new LoanException('Masih ada pengajuan perpanjangan yang menunggu keputusan untuk peminjaman ini.');
            }

            return $loan->extensions()->create([
                'requested_by' => $requester->id,
                'days' => $days,
                'reason' => $reason,
                'status' => ExtensionStatus::PENDING,
                'requested_at' => now(),
                'previous_due_at' => $loan->due_at,
            ]);
        });

        Activity::log('LOAN_EXTENSION_REQUESTED', "Extension of {$days} day(s) requested for loan {$loan->code}", $extension, $requester);

        $loan->member->user?->notify(new LoanExtensionSubmittedNotification($extension));

        User::permission('loan-extensions.approve')->get()->each(
            fn (User $staff) => $staff->notify(new NewLoanExtensionPendingNotification($extension))
        );

        return $extension;
    }
}
