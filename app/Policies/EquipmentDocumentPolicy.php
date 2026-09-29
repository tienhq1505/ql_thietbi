<?php

namespace App\Policies;

use App\Models\EquipmentDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'equipment_dept', 'technician', 'using_dept', 'directorate'])
            || $user->hasAnyPermission(['equipment.view', 'equipment.view_department']);
    }

    public function view(User $user, EquipmentDocument $document): bool
    {
        $equipment = $document->equipment;
        if (! $equipment) {
            return false;
        }

        if ($user->hasRole('admin') || $user->hasRole('directorate')) {
            return true;
        }

        if ($user->hasRole('using_dept')) {
            return $user->department_id && (int) $equipment->department_id === (int) $user->department_id;
        }

        if ($user->hasRole('equipment_dept') || $user->hasRole('technician') || $user->can('equipment.view')) {
            return true;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.edit');
    }

    public function update(User $user, EquipmentDocument $document): bool
    {
        return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.edit');
    }

    public function delete(User $user, EquipmentDocument $document): bool
    {
        return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.delete');
    }
}
