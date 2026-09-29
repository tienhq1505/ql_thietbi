<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Manufacturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'country',
        'website',
    ];

    public function equipmentModels(): HasMany
    {
        return $this->hasMany(EquipmentModel::class, 'manufacturer_id');
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(Equipment::class, 'manufacturer_id');
    }
}
