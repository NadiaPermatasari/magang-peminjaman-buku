<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BookCopyController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\LoanExtensionController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecurityDashboardController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
|
| Sign in / logout / password reset / email verification / 2FA / password
| confirmation routes are registered by Laravel Fortify (config/fortify.php
| + app/Providers/FortifyServiceProvider.php). Public self-registration is
| disabled (spec §58).
|
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    // Profile (edit / password forms post to Fortify; the rest is handled here)
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('profile/avatar', [ProfileController::class, 'updateAvatar'])->middleware('throttle:upload')->name('profile.avatar');
    Route::delete('profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::post('profile/sessions/logout-others', [ProfileController::class, 'logoutOtherSessions'])->name('profile.sessions.logout-others');
    // No password.confirm here: the controller already re-validates the
    // current password itself (current_password rule), which is equivalent
    // re-authentication without the redirect-loses-the-POST-body problem
    // password.confirm has on a bare action route with no preceding GET step.
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Katalog (spec §41 "Semua User") — read-only, not gated by a permission
    Route::get('catalog', [CatalogController::class, 'index'])->name('catalog.index');
    Route::get('catalog/{book}', [CatalogController::class, 'show'])->name('catalog.show');

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('notifications', [NotificationController::class, 'destroyAll'])->name('notifications.destroy-all');
    Route::post('notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Master Data (spec §41) — authorization is enforced per-action inside
    // each controller/FormRequest via the matching Policy (object-level
    // checks, not just route middleware — spec §26/§50).
    Route::get('book-copies/lookup', [BookCopyController::class, 'lookup'])->middleware('throttle:search')->name('book-copies.lookup');
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('racks', RackController::class)->except('show');
    Route::resource('books', BookController::class)->except('show')->middleware('throttle:upload');
    Route::resource('book-copies', BookCopyController::class)->except('show');

    // Keanggotaan
    Route::resource('members', MemberController::class)->except('show');
    Route::post('members/{member}/account', [MemberController::class, 'account'])->name('members.account');

    // Transaksi > Peminjaman (spec §41/§12-17). Static segments (pending,
    // ready, active, overdue, create) are registered before {loan} so they
    // are never swallowed by the UUID route-model-binding pattern.
    Route::get('loans/pending', [LoanController::class, 'pending'])->name('loans.pending');
    Route::get('loans/ready', [LoanController::class, 'ready'])->name('loans.ready');
    Route::get('loans/active', [LoanController::class, 'active'])->name('loans.active');
    Route::get('loans/overdue', [LoanController::class, 'overdue'])->name('loans.overdue');
    Route::get('loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('loans', [LoanController::class, 'store'])->name('loans.store');
    Route::get('loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('loans/{loan}', [LoanController::class, 'show'])->name('loans.show');
    Route::post('loans/{loan}/approve', [LoanController::class, 'approve'])->name('loans.approve');
    Route::post('loans/{loan}/reject', [LoanController::class, 'reject'])->name('loans.reject');
    // Penyerahan dan unggah bukti foto sama-sama menerima file gambar, jadi
    // keduanya lewat rate limiter upload (spec §32).
    Route::post('loans/{loan}/handover', [LoanController::class, 'handover'])->middleware('throttle:upload')->name('loans.handover');
    Route::post('loans/{loan}/items/{item}/photo', [LoanController::class, 'uploadHandoverPhoto'])->middleware('throttle:upload')->name('loans.items.photo');
    Route::post('loans/{loan}/cancel', [LoanController::class, 'cancel'])->name('loans.cancel');

    // Transaksi > Perpanjangan / Banding Peminjaman. Anggota mengajukan dari
    // halaman detail peminjaman, petugas/admin menyetujui atau menolak.
    Route::get('loan-extensions', [LoanExtensionController::class, 'index'])->name('loan-extensions.index');
    Route::post('loans/{loan}/extensions', [LoanExtensionController::class, 'store'])->name('loan-extensions.store');
    Route::post('loan-extensions/{extension}/approve', [LoanExtensionController::class, 'approve'])->name('loan-extensions.approve');
    Route::post('loan-extensions/{extension}/reject', [LoanExtensionController::class, 'reject'])->name('loan-extensions.reject');

    // Transaksi > Pengembalian. Petugas memilih eksemplar yang sedang
    // dipinjam lalu mengunggah foto bukti pengembalian (tanpa scan barcode).
    Route::get('returns', [ReturnController::class, 'index'])->name('returns.index');
    Route::post('returns', [ReturnController::class, 'store'])->middleware('throttle:upload')->name('returns.store');

    // Laporan (spec §45)
    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/loans', [ReportController::class, 'loans'])->name('reports.loans');
        Route::get('reports/returns', [ReportController::class, 'returns'])->name('reports.returns');
        Route::get('reports/overdue', [ReportController::class, 'overdue'])->name('reports.overdue');
        Route::get('reports/statistics', [ReportController::class, 'statistics'])->name('reports.statistics');
    });

    // Administrasi > User (spec §41). Role changes require re-authentication
    // (spec §6): password.confirm guards the GET view *and* the POST/PUT
    // that follows it, so confirmation happens before the form is filled —
    // guarding only the write route would silently drop the submitted data
    // on an unconfirmed session (RequirePassword redirects to the confirm
    // screen without preserving the original POST).
    Route::middleware('permission:users.view')->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
    });
    Route::middleware(['permission:users.create', 'password.confirm'])->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    Route::middleware(['permission:users.update', 'password.confirm'])->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware(['permission:users.disable', 'password.confirm'])->group(function () {
        Route::delete('users/{user}', [UserController::class, 'disable'])->name('users.disable');
    });

    // Administrasi > Role & Permission (spec §41). Same confirm-first
    // reasoning as Users above: changing a role's permission set is a
    // sensitive access-control action, so password.confirm guards the GET
    // view and the POST/PUT/DELETE together.
    Route::middleware('permission:roles.manage')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });
    Route::middleware(['permission:roles.manage', 'password.confirm'])->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    Route::middleware('permission:permissions.manage')->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
    });

    // Administrasi > Audit Log
    Route::middleware('permission:audit-logs.view')->group(function () {
        Route::get('audit-logs', [ActivityLogController::class, 'index'])->name('audit-logs.index');
    });

    // Security > Security Dashboard
    Route::middleware('permission:security-dashboard.view')->group(function () {
        Route::get('security-dashboard', [SecurityDashboardController::class, 'index'])->name('security-dashboard.index');
    });

    // Pengaturan — includes security settings (spec §6), same confirm-first
    // reasoning as the Users group above.
    Route::middleware(['permission:settings.manage', 'password.confirm'])->group(function () {
        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->middleware('throttle:upload')->name('settings.update');
    });
});
