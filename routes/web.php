<?php

use App\Http\Controllers\EquipmentLabelController;
use App\Http\Controllers\InventoryReportController;
use App\Http\Middleware\CheckDepartmentScope;
use App\Livewire\AuditLogs\AuditLogIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Equipment\EquipmentCreate;
use App\Livewire\Equipment\EquipmentEdit;
use App\Livewire\Equipment\EquipmentImport;
use App\Livewire\Equipment\EquipmentIndex;
use App\Livewire\Equipment\EquipmentScan;
use App\Livewire\Equipment\EquipmentShow;
use App\Livewire\Equipment\EquipmentUnassigned;
use App\Livewire\ImportBatches\ImportBatchIndex;
use App\Livewire\Inventory\InventoryIndex;
use App\Livewire\Inventory\InventoryReport;
use App\Livewire\Inventory\InventoryShow;
use App\Livewire\MasterDataIndex;
use App\Livewire\Profile\ChangePassword;
use App\Livewire\Reports\DocumentReportIndex;
use App\Livewire\Users\UserIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', Login::class)->name('login')->middleware('guest');

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout')->middleware('auth');

Route::middleware(['auth', CheckDepartmentScope::class])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/equipment', EquipmentIndex::class)->name('equipment.index');
    Route::get('/equipment/import', EquipmentImport::class)->name('equipment.import')->middleware('permission:equipment.import');
    Route::get('/equipment/create', EquipmentCreate::class)->name('equipment.create');
    Route::get('/equipment/unassigned', EquipmentUnassigned::class)->name('equipment.unassigned');
    Route::get('/equipment/{id}', EquipmentShow::class)->name('equipment.show');
    Route::get('/equipment/{equipment}/label', [EquipmentLabelController::class, 'single'])->name('equipment.label');
    Route::get('/equipment/{id}/edit', EquipmentEdit::class)->name('equipment.edit');
    Route::get('/q/{code}', EquipmentScan::class)->where('code', '.*')->name('equipment.scan');

    Route::get('/import-batches', ImportBatchIndex::class)->name('import-batches.index')->middleware('permission:equipment.import');

    Route::get('/categories', MasterDataIndex::class)->name('master-data.index');

    Route::get('/reports/documents', DocumentReportIndex::class)->name('reports.documents');

    Route::get('/audit-logs', AuditLogIndex::class)->name('audit-logs.index')->middleware('permission:audit_log.view');

    Route::get('/inventory', InventoryIndex::class)->name('inventory.index')->middleware('permission:inventory.manage');
    Route::get('/inventory/{session}', InventoryShow::class)->name('inventory.show')->middleware('permission:inventory.manage');
    Route::get('/inventory/{session}/report', InventoryReport::class)->name('inventory.report')->middleware('permission:inventory.manage');
    Route::get('/inventory/{session}/export-pdf', [InventoryReportController::class, 'exportPdf'])->name('inventory.export-pdf')->middleware('permission:inventory.manage');
    Route::get('/inventory/{session}/export-excel', [InventoryReportController::class, 'exportExcel'])->name('inventory.export-excel')->middleware('permission:inventory.manage');
    Route::get('/inventory/{session}/labels', [EquipmentLabelController::class, 'bySession'])->name('inventory.labels');

    Route::get('/departments/{department}/equipment-labels', [EquipmentLabelController::class, 'byDepartment'])->name('departments.equipment-labels');

    Route::get('/users', UserIndex::class)->name('users.index')->middleware('permission:users.manage');
    Route::get('/profile/password', ChangePassword::class)->name('profile.password');
});
