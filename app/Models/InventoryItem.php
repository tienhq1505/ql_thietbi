<?php

namespace App\Models;

use App\Enums\InventoryItemResultEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_session_id',
        'equipment_id',
        'scanned_code',
        'system_department_id',
        'system_location_id',
        'actual_location',
        'result',
        'note',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'result' => InventoryItemResultEnum::class,
        'checked_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(InventorySession::class, 'inventory_session_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function systemDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'system_department_id');
    }

    public function systemLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'system_location_id');
    }

    public function checker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
