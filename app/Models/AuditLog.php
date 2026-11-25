<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use RuntimeException;

class AuditLog extends Model
{
    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = null;

    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Audit logs are append-only and cannot be updated.');
        });

        static::deleting(function () {
            throw new RuntimeException('Audit logs are append-only and cannot be deleted.');
        });
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        throw new RuntimeException('Audit logs are append-only and cannot be updated.');
    }

    public function delete(): ?bool
    {
        throw new RuntimeException('Audit logs are append-only and cannot be deleted.');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Map model class to Vietnamese friendly label
     */
    public function getAuditableNameAttribute(): string
    {
        $class = class_basename($this->auditable_type);

        return match ($class) {
            'Equipment' => 'Thiết bị',
            'EquipmentDocument' => 'Hồ sơ thiết bị',
            'EquipmentConditionHistory' => 'Lịch sử tình trạng',
            'EquipmentDepartmentHistory' => 'Lịch sử chuyển khoa',
            'User' => 'Người dùng',
            'Department' => 'Khoa / Phòng',
            'EquipmentCategory' => 'Loại thiết bị',
            'Manufacturer' => 'Hãng sản xuất',
            'EquipmentModel' => 'Model thiết bị',
            'Supplier' => 'Nhà cung cấp',
            'Location' => 'Vị trí',
            'InventorySession' => 'Kỳ kiểm kê',
            'InventoryItem' => 'Mục kiểm kê',
            default => $class,
        };
    }

    /**
     * Map action to Vietnamese label
     */
    public function getActionNameAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Tạo mới',
            'updated' => 'Cập nhật',
            'deleted' => 'Xóa',
            default => $this->action,
        };
    }
}
