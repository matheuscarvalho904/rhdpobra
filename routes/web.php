<?php

use App\Http\Controllers\PayrollPayslipController;
use Illuminate\Support\Facades\Route;

Route::get('/folha/{payrollRun}/holerite/{employee}', [PayrollPayslipController::class, 'view'])
    ->name('payroll.payslip.view');

Route::get('/folha/{payrollRun}/holerite/{employee}/download', [PayrollPayslipController::class, 'download'])
    ->name('payroll.payslip.download');

Route::get('/folha/{payrollRun}/holerites/download-all', [PayrollPayslipController::class, 'downloadAll'])
    ->name('payroll.payslip.download-all');