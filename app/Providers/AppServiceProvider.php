<?php

namespace App\Providers;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentConditionHistory;
use App\Models\EquipmentDepartmentHistory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use App\Observers\AuditableObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $auditableModels = [
            Equipment::class,
            EquipmentDocument::class,
            EquipmentConditionHistory::class,
            EquipmentDepartmentHistory::class,
            User::class,
            Department::class,
            EquipmentCategory::class,
            Manufacturer::class,
            EquipmentModel::class,
            Supplier::class,
            Location::class,
            InventorySession::class,
            InventoryItem::class,
        ];

        foreach ($auditableModels as $modelClass) {
            $modelClass::observe(AuditableObserver::class);
        }
    }
}
