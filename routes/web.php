<?php

use App\Http\Middleware\CheckDepartmentScope;
use App\Livewire\Auth\Login;
use App\Livewire\Equipment\EquipmentCreate;
use App\Livewire\Equipment\EquipmentEdit;
use App\Livewire\Equipment\EquipmentImport;
use App\Livewire\Equipment\EquipmentIndex;
use App\Livewire\Equipment\EquipmentShow;
use App\Livewire\Equipment\EquipmentUnassigned;
use App\Livewire\ImportBatches\ImportBatchIndex;
use App\Livewire\MasterDataIndex;
use App\Livewire\Reports\DocumentReportIndex;
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
    Route::get('/equipment/{id}/edit', EquipmentEdit::class)->name('equipment.edit');

    Route::get('/import-batches', ImportBatchIndex::class)->name('import-batches.index')->middleware('permission:equipment.import');

    Route::get('/categories', MasterDataIndex::class)->name('master-data.index');

    Route::get('/reports/documents', DocumentReportIndex::class)->name('reports.documents');
});
