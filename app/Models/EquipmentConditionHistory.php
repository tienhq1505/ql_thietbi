<?php

namespace App\Models;

use App\Enums\EquipmentConditionEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentConditionHistory extends Model
{
    use HasFactory;

    protected $table = 'equipment_condition_history';

    protected $fillable = [
        'equipment_id',
        'old_condition',
        'new_condition',
        'reason',
        'note',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'old_condition' => EquipmentConditionEnum::class,
        'new_condition' => EquipmentConditionEnum::class,
        'checked_at' => 'datetime',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function checkedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }
}
