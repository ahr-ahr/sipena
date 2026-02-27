<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cookie;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\IT\UserManagementController;
use App\Http\Controllers\IT\AuditLogController;
use App\Http\Controllers\IT\SettingController;
use App\Http\Controllers\IT\KelasWaliController;
use App\Http\Controllers\IT\ITTicketController;
use App\Http\Controllers\Sarpras\SarprasLaporanController;
use App\Http\Controllers\TU\TULaporanController;
use App\Http\Controllers\Kepsek\KepsekLaporanController;
use App\Http\Controllers\BK\BKLaporanController;
use App\Http\Controllers\Kesiswaan\KesiswaanLaporanController;
use App\Http\Controllers\GlobalSearchController;

Route::get('/lang/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['id', 'en']), 404);

    return redirect()->back()->withCookie(
        cookie(
            'locale',
            $locale,
            60 * 24 * 365,
            '/',
            null,
            true,
            false,
            false,
            'lax'
        )
    );
})->name('lang.switch');

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/search', GlobalSearchController::class)->name('global.search');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

     // List notifications (paginated)
    Route::get('/notifications', [NotificationController::class, 'index']);
    // Unread count (badge, realtime polling, dll)
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    // Show single notification
    Route::get('/notifications/{recipient}', [NotificationController::class, 'show']);
    // Mark single notification as read
    Route::patch('/notifications/{recipient}/read', [NotificationController::class, 'markAsRead']);
    // Mark all notifications as read
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    // Delete notification
    Route::delete('/notifications/{recipient}', [NotificationController::class, 'destroy']);

    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::put('/laporan/{laporan}', [LaporanController::class, 'update'])->name('laporan.update');
    Route::get('/laporan/export/excel', [LaporanController::class, 'exportExcel'])->name('laporan.export.excel');
    Route::get('/laporan/export/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    Route::post('/laporan', [LaporanController::class, 'store'])->middleware('throttle:3,1')->name('laporan.store');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    // Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    // Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    Route::get('/tickets/export/excel', [TicketController::class, 'exportExcel'])->name('tickets.export.excel');
    Route::get('/tickets/export/pdf', [TicketController::class, 'exportPdf'])->name('tickets.export.pdf');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment'])->name('tickets.comment');

    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');
    Route::get('/history/export/excel', [HistoryController::class, 'exportExcel'])->name('history.export.excel');
    Route::post('/history/export/pdf', [HistoryController::class, 'exportPdf']);

    Route::get('/it/users', [UserManagementController::class, 'index'])->name('it.users.index');
    Route::patch('/it/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('it.users.role');
    Route::delete('/it/users/{user}', [UserManagementController::class, 'destroy'])->name('it.users.destroy');

    Route::get('/it/kelas-wali', [KelasWaliController::class, 'index'])->name('it.kelas-wali.index');
    Route::post('/it/kelas-wali', [KelasWaliController::class, 'store'])->name('it.kelas-wali.store');
    Route::delete('/it/kelas-wali', [KelasWaliController::class, 'destroy'])->name('it.kelas-wali.destroy');

    Route::get('/it/audit-logs', [AuditLogController::class, 'index'])->name('it.audit-logs.index');

    Route::get('/it/settings', [SettingController::class, 'edit'])->name('it.settings.edit');
    Route::post('/it/settings', [SettingController::class, 'update'])->name('it.settings.update');

    Route::get('/it/tickets', [ITTicketController::class, 'index'])->name('it.tickets.index');
    Route::get('/it/tickets/{ticket}', [ITTicketController::class, 'show'])->name('it.tickets.show');
    Route::patch('/it/tickets/{ticket}/resolve', [ITTicketController::class, 'resolve'])->name('it.tickets.resolve');
    Route::post('/it/tickets/{ticket}/comments', [ITTicketController::class, 'comment'])->name('it.tickets.comment');
    Route::get('/it/tickets/{ticket}/print', [ITTicketController::class, 'print'])->name('it.tickets.print');

    Route::get('/wali-kelas/laporan', [\App\Http\Controllers\Wali\LaporanController::class, 'index'])->name('wali.laporan.index');
    Route::post('/wali-kelas/laporan/{laporan}/approve', [\App\Http\Controllers\Wali\LaporanController::class, 'approve'])->name('wali.laporan.approve');
    Route::post('/wali-kelas/laporan/{laporan}/reject', [\App\Http\Controllers\Wali\LaporanController::class, 'reject'])->name('wali.laporan.reject');
    Route::get('/wali-kelas/laporan/export/excel', [\App\Http\Controllers\Wali\LaporanController::class, 'exportExcel'])->name('wali.laporan.export.excel');
    Route::get('/wali-kelas/laporan/export/pdf', [\App\Http\Controllers\Wali\LaporanController::class, 'exportPdf'])->name('wali.laporan.export.pdf');


    Route::get('/sarpras/laporan', [SarprasLaporanController::class, 'index'])->name('sarpras.laporan.index');
    Route::get('/sarpras/laporan/{laporan}', [SarprasLaporanController::class, 'show'])->name('sarpras.laporan.show');
    Route::patch('/sarpras/laporan/{laporan}/proses', [SarprasLaporanController::class, 'proses'])->name('sarpras.laporan.proses');
    Route::patch('/sarpras/laporan/{laporan}/selesai', [SarprasLaporanController::class, 'selesai'])->name('sarpras.laporan.selesai');
    Route::get('/sarpras/laporan/export/excel', [SarprasLaporanController::class, 'exportExcel'])->name('sarpras.laporan.export.excel');
    Route::get('/sarpras/laporan/export/pdf', [SarprasLaporanController::class, 'exportPdf'])->name('sarpras.laporan.export.pdf');

    Route::get('/tu/laporan', [TULaporanController::class, 'index'])->name('tu.laporan.index');
    Route::get('/tu/laporan/{laporan}', [TULaporanController::class, 'show'])->name('tu.laporan.show');
    Route::patch('/tu/laporan/{laporan}/setujui', [TULaporanController::class, 'setujui'])->name('tu.laporan.setujui');
    Route::patch('/tu/laporan/{laporan}/tolak', [TULaporanController::class, 'tolak'])->name('tu.laporan.tolak');
    Route::get('/tu/laporan/export/excel', [TULaporanController::class, 'exportExcel'])->name('tu.laporan.export.excel');
    Route::get('/tu/laporan/export/pdf', [TULaporanController::class, 'exportPdf'])->name('tu.laporan.export.pdf');

    Route::get('/kepsek/laporan', [KepsekLaporanController::class, 'index'])->name('kepsek.laporan.index');
    Route::get('/kepsek/laporan/{laporan}', [KepsekLaporanController::class, 'show'])->name('kepsek.laporan.show');
    Route::patch('/kepsek/laporan/{laporan}/sekepsekjui', [KepsekLaporanController::class, 'sekepsekjui'])->name('kepsek.laporan.sekepsekjui');
    Route::patch('/kepsek/laporan/{laporan}/tolak', [KepsekLaporanController::class, 'tolak'])->name('kepsek.laporan.tolak');
    Route::get('/kepsek/laporan/export/excel', [KepsekLaporanController::class, 'exportExcel'])->name('kepsek.laporan.export.excel');
    Route::get('/kepsek/laporan/export/pdf', [KepsekLaporanController::class, 'exportPdf'])->name('kepsek.laporan.export.pdf');

    Route::prefix('bk')->name('bk.')->group(function () {
        Route::get('/laporan', [BKLaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/{laporan}', [BKLaporanController::class, 'show'])->name('laporan.show');
        Route::patch('/laporan/{laporan}/proses', [BKLaporanController::class, 'proses'])->name('laporan.proses');
        Route::patch('/laporan/{laporan}/selesai', [BKLaporanController::class, 'selesai'])->name('laporan.selesai');
        Route::patch('/laporan/{laporan}/tolak', [BKLaporanController::class, 'tolak'])->name('laporan.tolak');

        Route::get('/laporan/export/excel', [BKLaporanController::class, 'exportExcel'])->name('laporan.export.excel');
        Route::get('/laporan/export/pdf', [BKLaporanController::class, 'exportPdf'])->name('laporan.export.pdf');
    });

    Route::prefix('kesiswaan')->name('kesiswaan.')->group(function () {
            Route::get('/laporan', [KesiswaanLaporanController::class, 'index'])
                ->name('laporan.index');

            Route::get('/laporan/{laporan}', [KesiswaanLaporanController::class, 'show'])
                ->name('laporan.show');

            Route::post('/laporan/{laporan}/tindak-langsung', [KesiswaanLaporanController::class, 'tindakLangsung'])
                ->name('laporan.tindak');

            Route::post('/laporan/{laporan}/teruskan-sarpras', [KesiswaanLaporanController::class, 'teruskanKeSarpras'])
                ->name('laporan.teruskan');

            Route::post('/laporan/{laporan}/tolak', [KesiswaanLaporanController::class, 'tolak'])
                ->name('laporan.tolak');
        });
});

require __DIR__.'/auth.php';
