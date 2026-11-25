<?php

namespace App\Services;

use App\Enums\InventoryItemResultEnum;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;

class InventoryReportService
{
    /**
     * Check whether the given user has permission to access inventory session reports.
     * Aborts with 403 if not authorized.
     */
    public function authorizeAccess(User $user, InventorySession $session): void
    {
        if ($user->hasRole('directorate') || ! $user->can('inventory.manage')) {
            abort(403, 'Bạn không có quyền truy cập báo cáo kiểm kê.');
        }

        if ($user->hasRole('using_dept')) {
            if (! $user->department_id || ! $session->department_id || (int) $session->department_id !== (int) $user->department_id) {
                abort(403, 'Bạn không có quyền truy cập kỳ kiểm kê của khoa khác.');
            }
        }
    }

    /**
     * Apply department scope filter for using_dept users.
     */
    public function applyDepartmentScope(Builder|Relation $query, User $user): Builder|Relation
    {
        if ($user->hasRole('using_dept')) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('equipment_id')
                    ->orWhereHas('equipment', function ($eqQ) use ($user) {
                        $eqQ->where('department_id', $user->department_id);
                    });
            });
        }

        return $query;
    }

    /**
     * Calculate reconciliation statistics for a session, scoped by user permissions.
     *
     * @return array{total: int, matched: int, missing: int, excess: int, broken: int, untested: int, checked_total: int, progress_pct: float}
     */
    public function calculateStats(InventorySession $session, User $user): array
    {
        $baseQuery = $this->applyDepartmentScope($session->items(), $user);

        $total = (clone $baseQuery)->count();
        $matched = (clone $baseQuery)->where('result', InventoryItemResultEnum::KHOP->value)->count();
        $missing = (clone $baseQuery)->where('result', InventoryItemResultEnum::THIEU->value)->count();
        $excess = (clone $baseQuery)->where('result', InventoryItemResultEnum::THUA->value)->count();
        $broken = (clone $baseQuery)->where('result', InventoryItemResultEnum::HONG->value)->count();
        $untested = (clone $baseQuery)->whereNull('result')->count();
        $checkedTotal = $total - $untested;
        $progressPct = $total > 0 ? round(($checkedTotal / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'matched' => $matched,
            'missing' => $missing,
            'excess' => $excess,
            'broken' => $broken,
            'untested' => $untested,
            'checked_total' => $checkedTotal,
            'progress_pct' => $progressPct,
        ];
    }

    /**
     * Get discrepancy items (thiếu, thừa, hỏng) filtered by department scope and EquipmentPolicy::view.
     */
    public function getDiscrepancyItems(InventorySession $session, User $user): Collection
    {
        $query = InventoryItem::with([
            'equipment.equipmentModel',
            'equipment.category',
            'systemDepartment',
            'systemLocation',
            'checker',
        ])
            ->where('inventory_session_id', $session->id)
            ->whereIn('result', [
                InventoryItemResultEnum::THIEU->value,
                InventoryItemResultEnum::THUA->value,
                InventoryItemResultEnum::HONG->value,
            ]);

        $this->applyDepartmentScope($query, $user);

        return $query->orderBy('result')->orderBy('id')->get()
            ->filter(fn ($item) => ! $item->equipment || $user->can('view', $item->equipment));
    }
}
