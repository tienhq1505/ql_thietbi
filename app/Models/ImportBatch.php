<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'imported_by',
        'total_rows',
        'success_rows',
        'failed_rows',
        'error_details',
        'status',
    ];

    protected $casts = [
        'error_details' => 'array',
        'total_rows' => 'integer',
        'success_rows' => 'integer',
        'failed_rows' => 'integer',
    ];

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'imported_by');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'import_batch_id');
    }
}
