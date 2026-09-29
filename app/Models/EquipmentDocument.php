<?php

namespace App\Models;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'equipment_id',
        'document_type',
        'status',
        'file_path',
        'valid_until',
        'note',
    ];

    protected $casts = [
        'document_type' => DocumentTypeEnum::class,
        'status' => DocumentStatusEnum::class,
        'valid_until' => 'date',
    ];

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }
}
