<?php

namespace App\Services;

use App\Enums\EquipmentConditionEnum;
use App\Models\Equipment;
use App\Models\EquipmentConditionHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EquipmentConditionService
{
    /**
     * Update equipment condition and record history in a transaction.
     * If the equipment is already in the target condition, does nothing and returns null.
     */
    public function updateCondition(
        Equipment $equipment,
        EquipmentConditionEnum|string $newCondition,
        string $reason,
        ?string $note = null,
        ?User $user = null
    ): ?EquipmentConditionHistory {
        $user = $user ?? auth()->user();

        $newConditionValue = $newCondition instanceof EquipmentConditionEnum
            ? $newCondition->value
            : $newCondition;

        $oldConditionValue = $equipment->condition instanceof EquipmentConditionEnum
            ? $equipment->condition->value
            : $equipment->condition;

        // If condition has not changed, do nothing
        if ($oldConditionValue === $newConditionValue) {
            return null;
        }

        return DB::transaction(function () use ($equipment, $oldConditionValue, $newConditionValue, $reason, $note, $user) {
            $equipment->update([
                'condition' => $newConditionValue,
            ]);

            return EquipmentConditionHistory::create([
                'equipment_id' => $equipment->id,
                'old_condition' => $oldConditionValue,
                'new_condition' => $newConditionValue,
                'reason' => $reason,
                'note' => $note ?: null,
                'checked_by' => $user?->id,
                'checked_at' => now(),
            ]);
        });
    }
}
