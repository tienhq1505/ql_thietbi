<?php

namespace App\Models;

use App\Enums\InventorySessionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventorySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'department_id',
        'start_date',
        'end_date',
        'status',
        'created_by',
        'completed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'completed_at' => 'datetime',
        'status' => InventorySessionStatusEnum::class,
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === InventorySessionStatusEnum::COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === InventorySessionStatusEnum::IN_PROGRESS;
    }

    public function getScopeNameAttribute(): string
    {
        return $this->department ? $this->department->name : 'Toàn bệnh viện';
    }
}
