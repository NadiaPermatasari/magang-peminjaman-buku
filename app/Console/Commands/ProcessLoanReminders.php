<?php

namespace App\Console\Commands;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\LoanReminder;
use App\Notifications\LoanDueReminderNotification;
use App\Notifications\LoanOverdueNotification;
use App\Notifications\LoanPickupReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;

/**
 * Sends pickup/due/overdue reminder notifications based on settings
 * (spec §19/§20 — due_reminder_days, overdue_reminder_days,
 * pickup_reminder_hours). Idempotent: each (loan, type, milestone) is
 * recorded in loan_reminders and never sent twice, so this is safe to run
 * as often as the schedule likes (e.g. hourly).
 */
class ProcessLoanReminders extends Command
{
    protected $signature = 'loans:process-reminders';

    protected $description = 'Send pickup, due-date and overdue reminder notifications';

    public function handle(): int
    {
        $sent = 0;
        $sent += $this->pickupReminders();
        $sent += $this->dueReminders();
        $sent += $this->overdueReminders();

        $this->info("Sent {$sent} reminder notification(s).");

        return self::SUCCESS;
    }

    private function pickupReminders(): int
    {
        $thresholdHours = (int) setting('pickup_reminder_hours', 24);
        $sent = 0;

        $loans = Loan::where('status', LoanStatus::APPROVED)
            ->whereNotNull('pickup_deadline')
            ->where('pickup_deadline', '>', now())
            ->where('pickup_deadline', '<=', now()->addHours($thresholdHours))
            ->with('member.user')
            ->get();

        foreach ($loans as $loan) {
            if ($this->remember($loan->id, 'PICKUP', $thresholdHours)) {
                $loan->member->user?->notify(new LoanPickupReminderNotification($loan));
                $sent++;
            }
        }

        return $sent;
    }

    private function dueReminders(): int
    {
        $days = collect(setting('due_reminder_days', [3, 1, 0]))->map(fn ($d) => (int) $d);
        $sent = 0;

        if ($days->isEmpty()) {
            return 0;
        }

        $loans = Loan::where('status', LoanStatus::BORROWED)
            ->whereNotNull('due_at')
            ->with('member.user')
            ->get();

        foreach ($loans as $loan) {
            $daysRemaining = (int) now()->startOfDay()->diffInDays($loan->due_at->copy()->startOfDay(), false);

            if ($daysRemaining < 0 || ! $days->contains($daysRemaining)) {
                continue;
            }

            if ($this->remember($loan->id, 'DUE', $daysRemaining)) {
                $loan->member->user?->notify(new LoanDueReminderNotification($loan, $daysRemaining));
                $sent++;
            }
        }

        return $sent;
    }

    private function overdueReminders(): int
    {
        $days = collect(setting('overdue_reminder_days', [1, 3, 7]))->map(fn ($d) => (int) $d);
        $sent = 0;

        if ($days->isEmpty()) {
            return 0;
        }

        $loans = Loan::where('status', LoanStatus::OVERDUE)
            ->whereNotNull('due_at')
            ->with('member.user')
            ->get();

        foreach ($loans as $loan) {
            $lateDays = (int) $loan->due_at->copy()->startOfDay()->diffInDays(now()->startOfDay());

            if (! $days->contains($lateDays)) {
                continue;
            }

            if ($this->remember($loan->id, 'OVERDUE', $lateDays)) {
                $loan->member->user?->notify(new LoanOverdueNotification($loan, $lateDays));
                $sent++;
            }
        }

        return $sent;
    }

    /** Returns true (and records it) only the first time this milestone is seen. */
    private function remember(int $loanId, string $type, int $milestone): bool
    {
        try {
            LoanReminder::create([
                'loan_id' => $loanId,
                'type' => $type,
                'milestone' => $milestone,
                'sent_at' => now(),
            ]);

            return true;
        } catch (QueryException $e) {
            // Unique constraint hit: already sent for this milestone.
            return false;
        }
    }
}
