<?php

use App\Console\Commands\ExpireApprovedLoans;
use App\Console\Commands\MarkOverdueLoans;
use App\Console\Commands\ProcessLoanReminders;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled jobs (spec §20)
|--------------------------------------------------------------------------
|
| All three commands are idempotent — safe to run as often as this
| schedule fires without side effects from double-processing. Requires a
| real cron entry running `php artisan schedule:run` every minute in
| production (see README "Deployment").
|
*/

Schedule::command(MarkOverdueLoans::class)->hourly()->withoutOverlapping();
Schedule::command(ExpireApprovedLoans::class)->hourly()->withoutOverlapping();
Schedule::command(ProcessLoanReminders::class)->hourly()->withoutOverlapping();
