<?php

use App\Http\Controllers\PayrollPayslipController;
use App\Models\SystemBackup;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;


Route::get('/folha/{payrollRun}/holerite/{employee}', [PayrollPayslipController::class, 'view'])
    ->name('payroll.payslip.view');

Route::get('/folha/{payrollRun}/holerite/{employee}/download', [PayrollPayslipController::class, 'download'])
    ->name('payroll.payslip.download');

Route::get('/folha/{payrollRun}/holerites/download-all', [PayrollPayslipController::class, 'downloadAll'])
    ->name('payroll.payslip.download-all');

Route::get('/system-backups/{systemBackup}/download', function (SystemBackup $systemBackup): StreamedResponse {
    abort_unless(Auth::check(), 403);
    abort_unless($systemBackup->status === 'success', 404);
    abort_unless(filled($systemBackup->path), 404);

    $disk = $systemBackup->disk ?: env('BACKUP_DISK', 'local');

    abort_unless(Storage::disk($disk)->exists($systemBackup->path), 404);

    return response()->streamDownload(function () use ($disk, $systemBackup): void {
        echo Storage::disk($disk)->get($systemBackup->path);
    }, basename($systemBackup->path));
})->name('system-backups.download');