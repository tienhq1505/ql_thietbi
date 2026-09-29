<?php

namespace App\Models;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Equipment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'equipment';

    protected $fillable = [
        'code',
        'name',
        'equipment_model_id',
        'category_id',
        'manufacturer_id',
        'serial_number',
        'serial_pending',
        'manufacture_year',
        'origin_country',
        'import_date',
        'usage_start_date',
        'purchase_price',
        'funding_source',
        'supplier_id',
        'warranty_expiration_date',
        'department_id',
        'location_id',
        'manager_id',
        'manager_name',
        'status',
        'condition',
        'ownership_type',
        'origin_model_text',
        'source_ref',
        'delete_requested_at',
        'delete_requested_by',
        'import_batch_id',
        'note',
        'deleted_by',
    ];

    protected $casts = [
        'status' => EquipmentStatusEnum::class,
        'condition' => EquipmentConditionEnum::class,
        'ownership_type' => OwnershipTypeEnum::class,
        'serial_pending' => 'boolean',
        'import_date' => 'date',
        'usage_start_date' => 'date',
        'warranty_expiration_date' => 'date',
        'delete_requested_at' => 'datetime',
        'purchase_price' => 'decimal:2',
    ];

    public function equipmentModel(): BelongsTo
    {
        return $this->belongsTo(EquipmentModel::class, 'equipment_model_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EquipmentCategory::class, 'category_id');
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function deleteRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delete_requested_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function importBatch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }

    public function conditionHistories(): HasMany
    {
        return $this->hasMany(EquipmentConditionHistory::class)->orderByDesc('checked_at')->orderByDesc('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EquipmentDocument::class);
    }

    public function departmentHistories(): HasMany
    {
        return $this->hasMany(EquipmentDepartmentHistory::class)->orderByDesc('created_at')->orderByDesc('id');
    }
}
