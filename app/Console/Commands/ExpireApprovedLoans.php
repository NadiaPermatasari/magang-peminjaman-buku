<?php

namespace App\Console\Commands;

use App\Actions\Loans\ExpireLoan;
use App\Enums\LoanStatus;
use App\Models\Loan;
use Illuminate\Console\Command;

/**
 * Releases APPROVED loans whose pickup_deadline has passed without the
 * member collecting the book(s) (spec §17/§20).
 */
class ExpireApprovedLoans extends Command
{
    protected $signature = 'loans:expire-approved';

    protected $description = 'Expire approved loans that were not picked up before the deadline';

    public function handle(ExpireLoan $action): int
    {
        $loans = Loan::where('status', LoanStatus::APPROVED)
            ->where('pickup_deadline', '<', now())
            ->get();

        foreach ($loans as $loan) {
            $action->handle($loan);
        }

        $this->info("Expired {$loans->count()} approved loan(s).");

        return self::SUCCESS;
    }
}
