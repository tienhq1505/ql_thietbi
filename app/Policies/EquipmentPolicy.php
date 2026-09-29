<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EquipmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'equipment_dept', 'technician', 'using_dept', 'directorate'])
            || $user->hasAnyPermission(['equipment.view', 'equipment.view_department']);
    }

    public function view(User $user, Equipment $equipment): bool
    {
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
        return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.create');
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.edit');
    }

    public function updateCondition(User $user, Equipment $equipment): bool
    {
        return $this->update($user, $equipment)
            || $user->hasRole('technician')
            || $user->can('equipment.update_condition');
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('admin') || $user->can('equipment.delete');
    }

    public function requestDelete(User $user, Equipment $equipment): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('equipment_dept')) {
            return true;
        }

        if ($user->hasRole('using_dept')) {
            return $user->department_id && (int) $equipment->department_id === (int) $user->department_id;
        }

        return $user->can('equipment.request_delete');
    }

    public function import(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('equipment_dept')
            || $user->can('equipment.import');
    }

    public function export(User $user): bool
    {
        return $user->hasRole('admin')
            || $user->hasRole('equipment_dept')
            || $user->hasRole('directorate')
            || $user->can('equipment.export');
    }
}
