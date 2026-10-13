<?php

namespace App\Http\Middleware;

use App\Models\Loan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menjaga status "Terlambat" tetap mutakhir tanpa bergantung pada cron:
 * paling sering sekali per menit, hanya untuk pengguna yang sudah login.
 */
class SyncOverdueLoans
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->isMethodSafe() && Cache::add('loans:overdue-synced', true, 60)) {
            Loan::syncOverdue();
        }

        return $next($request);
    }
}
