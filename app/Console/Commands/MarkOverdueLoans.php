<?php

namespace App\Console\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Support\Activity;
use Illuminate\Console\Command;

/**
 * Flips BORROWED loans past their due date to OVERDUE (spec §17) — status
 * transition only. Overdue *reminder notifications* are a separate concern
 * handled by loans:process-reminders (spec §19/§20). Safe to run
 * repeatedly — only touches loans still in BORROWED status.
 */
class MarkOverdueLoans extends Command
{
    protected $signature = 'loans:mark-overdue';

    protected $description = 'Mark borrowed loans past their due date as overdue';

    public function handle(): int
    {
        $loans = Loan::where('status', LoanStatus::BORROWED)
            ->where('due_at', '<', now())
            ->get();

        foreach ($loans as $loan) {
            $loan->items()->where('status', LoanStatus::BORROWED)->update(['status' => LoanStatus::OVERDUE]);
            $loan->update(['status' => LoanStatus::OVERDUE]);

            Activity::log('LOAN_OVERDUE', "Loan {$loan->code} marked overdue", $loan);
        }

        $this->info("Marked {$loans->count()} loan(s) as overdue.");

        return self::SUCCESS;
    }
}
