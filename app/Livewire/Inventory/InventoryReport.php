<?php

namespace App\Livewire\Inventory;

use App\Enums\InventoryItemResultEnum;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Services\InventoryReportService;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryReport extends Component
{
    use WithPagination;

    public InventorySession $session;

    public string $search = '';

    public string $resultFilter = '';

    public function mount(InventorySession|int $session): void
    {
        $user = auth()->user();

        if (! $user) {
            abort(403, 'Bạn không có quyền truy cập báo cáo kiểm kê.');
        }

        if (is_int($session)) {
            $this->session = InventorySession::with(['department', 'creator'])->findOrFail($session);
        } else {
            $this->session = $session->loadMissing(['department', 'creator']);
        }

        app(InventoryReportService::class)->authorizeAccess($user, $this->session);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedResultFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $reportService = app(InventoryReportService::class);

        // Stats calculation via shared service
        $stats = $reportService->calculateStats($this->session, $user);

        // Query items for paginated list
        $itemsQuery = InventoryItem::with([
            'equipment.equipmentModel',
            'equipment.category',
            'systemDepartment',
            'systemLocation',
            'checker',
        ])
            ->where('inventory_session_id', $this->session->id);

        $reportService->applyDepartmentScope($itemsQuery, $user);

        if ($this->search) {
            $search = $this->search;
            $itemsQuery->where(function ($q) use ($search) {
                $q->where('scanned_code', 'like', "%{$search}%")
                    ->orWhereHas('equipment', function ($eqQ) use ($search) {
                        $eqQ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%")
                            ->orWhere('serial_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($this->resultFilter) {
            if ($this->resultFilter === 'untested') {
                $itemsQuery->whereNull('result');
            } elseif ($this->resultFilter === 'discrepancy') {
                $itemsQuery->whereIn('result', [
                    InventoryItemResultEnum::THIEU->value,
                    InventoryItemResultEnum::THUA->value,
                    InventoryItemResultEnum::HONG->value,
                ]);
            } else {
                $itemsQuery->where('result', $this->resultFilter);
            }
        }

        $items = $itemsQuery
            ->orderByRaw('CASE WHEN result IS NULL THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->paginate(20);

        return view('livewire.inventory.inventory-report', [
            'items' => $items,
            'totalCount' => $stats['total'],
            'matchedCount' => $stats['matched'],
            'missingCount' => $stats['missing'],
            'excessCount' => $stats['excess'],
            'brokenCount' => $stats['broken'],
            'untestedCount' => $stats['untested'],
            'checkedTotal' => $stats['checked_total'],
            'progressPct' => $stats['progress_pct'],
        ])->layout('components.layouts.app', ['title' => 'Báo cáo đối soát: '.$this->session->name.' - MEMS']);
    }
}
