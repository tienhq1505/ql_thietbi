<?php

namespace App\Http\Controllers;

use App\Enums\EquipmentStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\InventorySession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class EquipmentLabelController
{
    use AuthorizesRequests;

    /**
     * Print QR label for a single equipment.
     */
    public function single(Equipment $equipment)
    {
        $this->authorize('view', $equipment);

        $equipment->loadMissing(['department', 'equipmentModel']);

        $labels = collect([
            $this->buildLabelData($equipment),
        ]);

        return view('equipment.labels', [
            'labels' => $labels,
            'title' => 'Nhãn QR: '.$equipment->code,
        ]);
    }

    /**
     * Print QR labels for all equipment in an inventory session.
     */
    public function bySession(Request $request, InventorySession $session)
    {
        $user = $request->user();

        // Directorate has no inventory access
        if ($user->hasRole('directorate')) {
            abort(403, 'Ban Giám đốc không có quyền truy cập tính năng Kiểm kê.');
        }

        if (! $user->can('inventory.manage')) {
            abort(403, 'Bạn không có quyền truy cập tính năng Kiểm kê.');
        }

        // using_dept scope check (same logic as InventoryShow)
        if ($user->hasRole('using_dept')) {
            if (! $user->department_id || ! $session->department_id || $session->department_id !== $user->department_id) {
                abort(403, 'Bạn không có quyền truy cập kỳ kiểm kê của khoa khác.');
            }
        }

        // Load items with equipment, filter out deleted equipment and da_thanh_ly
        $items = $session->items()
            ->with(['equipment.department', 'equipment.equipmentModel'])
            ->whereNotNull('equipment_id')
            ->whereHas('equipment', function ($q) {
                $q->where('status', '!=', EquipmentStatusEnum::DA_THANH_LY->value);
            })
            ->get();

        // Filter by EquipmentPolicy::view for each item
        $labels = $items
            ->filter(fn ($item) => $item->equipment && $user->can('view', $item->equipment))
            ->map(fn ($item) => $this->buildLabelData($item->equipment))
            ->values();

        return view('equipment.labels', [
            'labels' => $labels,
            'title' => 'Nhãn QR: '.$session->name,
        ]);
    }

    /**
     * Print QR labels for all equipment in a department.
     */
    public function byDepartment(Request $request, Department $department)
    {
        $user = $request->user();

        // using_dept scope check
        if ($user->hasRole('using_dept')) {
            if (! $user->department_id || (int) $department->id !== (int) $user->department_id) {
                abort(403, 'Khoa sử dụng chỉ được in nhãn thiết bị khoa mình.');
            }
        }

        $equipments = Equipment::with(['department', 'equipmentModel'])
            ->where('department_id', $department->id)
            ->where('status', '!=', EquipmentStatusEnum::DA_THANH_LY->value)
            ->get();

        // Filter by EquipmentPolicy::view
        $labels = $equipments
            ->filter(fn ($eq) => $user->can('view', $eq))
            ->map(fn ($eq) => $this->buildLabelData($eq))
            ->values();

        return view('equipment.labels', [
            'labels' => $labels,
            'title' => 'Nhãn QR: '.$department->name,
        ]);
    }

    /**
     * Build label data array for a single equipment.
     */
    protected function buildLabelData(Equipment $equipment): array
    {
        $url = rtrim(config('app.url'), '/').'/q/'.rawurlencode($equipment->code);
        $svg = (string) QrCode::size(120)->generate($url);
        $svg = preg_replace('/<\?xml.*?\x3F\x3E/is', '', $svg);

        return [
            'code' => $equipment->code,
            'url' => $url,
            'name' => $equipment->name,
            'department' => $equipment->department?->name ?? '—',
            'model' => $equipment->equipmentModel?->name ?? '—',
            'svg' => trim($svg),
        ];
    }
}
