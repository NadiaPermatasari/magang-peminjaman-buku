<?php

namespace App\Console\Commands;

use App\Models\Loan;
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
        $this->info('Marked '.Loan::syncOverdue().' loan(s) as overdue.');

        return self::SUCCESS;
    }
}
