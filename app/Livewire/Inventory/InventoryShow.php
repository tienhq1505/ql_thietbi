<?php

namespace App\Livewire\Inventory;

use App\Enums\EquipmentConditionEnum;
use App\Enums\InventoryItemResultEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Services\EquipmentConditionService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryShow extends Component
{
    use AuthorizesRequests, WithPagination;

    public InventorySession $session;

    // Scan & Manual input state
    public string $manualCode = '';

    public string $actualLocation = '';

    public string $scanNote = '';

    public ?string $scanSuccessMessage = null;

    public ?string $scanWarningMessage = null;

    public ?string $scanErrorMessage = null;

    // Mark broken modal
    public bool $showBrokenModal = false;

    public ?int $selectedItemIdForBroken = null;

    public string $brokenReason = '';

    public string $brokenNote = '';

    // Filters for items table
    public string $searchItem = '';

    public string $resultFilter = '';

    public function mount(InventorySession|int $session): void
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('directorate') || ! $user->can('inventory.manage')) {
            abort(403, 'Bạn không có quyền truy cập tính năng Kiểm kê.');
        }

        if (is_int($session)) {
            $this->session = InventorySession::with(['department', 'creator'])->findOrFail($session);
        } else {
            $this->session = $session->loadMissing(['department', 'creator']);
        }

        // Check department scope for using_dept
        if ($user->hasRole('using_dept')) {
            if (! $user->department_id || ! $this->session->department_id || $this->session->department_id !== $user->department_id) {
                abort(403, 'Bạn không có quyền truy cập kỳ kiểm kê của khoa khác.');
            }
        }
    }

    public function updatedSearchItem(): void
    {
        $this->resetPage();
    }

    public function updatedResultFilter(): void
    {
        $this->resetPage();
    }

    /**
     * Entry point for camera scanner
     */
    public function handleScannedCode(string $code): void
    {
        $this->processCode($code);
    }

    /**
     * Entry point for manual text input
     */
    public function submitManualCode(): void
    {
        $this->processCode($this->manualCode);
    }

    /**
     * Clear all scan status messages
     */
    protected function clearScanMessages(): void
    {
        $this->scanSuccessMessage = null;
        $this->scanWarningMessage = null;
        $this->scanErrorMessage = null;
    }

    /**
     * Tách mã thiết bị từ chuỗi quét được (hỗ trợ cả đường dẫn .../q/{code} lẫn mã thô)
     */
    protected function extractEquipmentCode(string $input): string
    {
        $input = trim($input);

        // Kiểm tra nếu là URL dạng .../q/{code}
        if (preg_match('~/q/([^/?#]+)~i', $input, $matches)) {
            return rawurldecode($matches[1]);
        }

        return $input;
    }

    /**
     * Process scanned/entered equipment code
     */
    protected function processCode(string $code): void
    {
        $this->clearScanMessages();
        $code = $this->extractEquipmentCode($code);

        if (empty($code)) {
            return;
        }

        // Server-side check: completed session cannot be edited
        if ($this->session->isCompleted()) {
            abort(403, 'Kỳ kiểm kê đã được chốt hoàn tất, không thể quét hoặc sửa đổi dữ liệu.');
        }

        $equipment = Equipment::where('code', $code)->first();

        // 1. Code does not exist in system
        if (! $equipment) {
            $this->scanErrorMessage = "Mã thiết bị '{$code}' không tồn tại trong hệ thống!";
            session()->flash('scan_error', $this->scanErrorMessage);

            return;
        }

        // 2. EquipmentPolicy view check
        if (! auth()->user()->can('view', $equipment)) {
            $this->scanErrorMessage = 'Mã không thuộc phạm vi kiểm kê của bạn.';
            session()->flash('scan_error', $this->scanErrorMessage);

            return;
        }

        // 3. Check if equipment is already in the inventory session
        $existingItem = InventoryItem::where('inventory_session_id', $this->session->id)
            ->where('equipment_id', $equipment->id)
            ->first();

        if ($existingItem) {
            // Check duplicate scan
            if ($existingItem->result !== null) {
                $this->scanWarningMessage = "Thiết bị '{$equipment->name}' ({$equipment->code}) đã được kiểm trước đó với kết quả: {$existingItem->result->label()}.";
                session()->flash('scan_warning', $this->scanWarningMessage);

                return;
            }

            // Mark as matched
            $existingItem->update([
                'result' => InventoryItemResultEnum::KHOP,
                'actual_location' => $this->actualLocation ?: null,
                'note' => $this->scanNote ?: null,
                'checked_by' => auth()->id(),
                'checked_at' => now(),
            ]);

            $this->scanSuccessMessage = "✓ Đã xác nhận KHỚP thiết bị: {$equipment->name} ({$equipment->code})";
            session()->flash('scan_success', $this->scanSuccessMessage);
            $this->reset(['manualCode', 'actualLocation', 'scanNote']);

            return;
        }

        // 4. Equipment not in this session list -> Excess item (THỪA)
        try {
            InventoryItem::create([
                'inventory_session_id' => $this->session->id,
                'equipment_id' => $equipment->id,
                'scanned_code' => $equipment->code,
                'system_department_id' => $equipment->department_id,
                'system_location_id' => $equipment->location_id,
                'actual_location' => $this->actualLocation ?: null,
                'result' => InventoryItemResultEnum::THUA,
                'note' => $this->scanNote ?: 'Thiết bị thừa / ngoài danh mục kế hoạch kiểm kê',
                'checked_by' => auth()->id(),
                'checked_at' => now(),
            ]);

            $this->scanWarningMessage = "Đã ghi nhận thiết bị THỪA: {$equipment->name} ({$equipment->code})";
            session()->flash('scan_warning', $this->scanWarningMessage);
            $this->reset(['manualCode', 'actualLocation', 'scanNote']);
        } catch (QueryException $e) {
            $this->scanWarningMessage = "Thiết bị '{$equipment->name}' ({$equipment->code}) đã tồn tại trong đợt kiểm kê này.";
            session()->flash('scan_warning', $this->scanWarningMessage);
        }
    }

    /**
     * Open modal to mark item as broken
     */
    public function openBrokenModal(int $itemId): void
    {
        if ($this->session->isCompleted()) {
            abort(403, 'Kỳ kiểm kê đã được chốt hoàn tất, không thể sửa đổi.');
        }

        $item = $this->session->items()->findOrFail($itemId);

        $this->selectedItemIdForBroken = $item->id;
        $this->brokenReason = 'Hỏng hóc phát hiện qua kiểm kê ngày '.now()->format('d/m/Y');
        $this->brokenNote = '';
        $this->showBrokenModal = true;
    }

    public function closeBrokenModal(): void
    {
        $this->showBrokenModal = false;
        $this->selectedItemIdForBroken = null;
        $this->brokenReason = '';
        $this->brokenNote = '';
    }

    /**
     * Confirm marking equipment as broken
     */
    public function confirmMarkBroken(EquipmentConditionService $conditionService): void
    {
        if ($this->session->isCompleted()) {
            abort(403, 'Kỳ kiểm kê đã được chốt hoàn tất, không thể sửa đổi.');
        }

        $this->validate([
            'brokenReason' => 'required|string|max:255',
            'brokenNote' => 'nullable|string|max:1000',
        ], [
            'brokenReason.required' => 'Vui lòng nhập mô tả tình trạng hỏng.',
        ]);

        $item = $this->session->items()->with('equipment')->findOrFail($this->selectedItemIdForBroken);
        $equipment = $item->equipment;

        // 1. Update inventory item result to HONG
        $item->update([
            'result' => InventoryItemResultEnum::HONG,
            'note' => $this->brokenReason.($this->brokenNote ? ' (Ghi chú: '.$this->brokenNote.')' : ''),
            'checked_by' => auth()->id(),
            'checked_at' => now(),
        ]);

        // 2. Check if user is authorized to update equipment condition
        $user = auth()->user();
        if ($equipment && $user->can('updateCondition', $equipment)) {
            // Update equipment condition & record history via shared service
            $conditionService->updateCondition(
                $equipment,
                EquipmentConditionEnum::HONG,
                $this->brokenReason,
                $this->brokenNote,
                $user
            );
        }

        $this->clearScanMessages();
        $this->scanSuccessMessage = "Đã đánh dấu HỎNG thiết bị: {$item->equipment?->name} ({$item->scanned_code})";
        session()->flash('scan_success', $this->scanSuccessMessage);
        $this->closeBrokenModal();
    }

    /**
     * Complete and close the inventory session
     */
    public function completeSession(): void
    {
        $user = auth()->user();

        // 1. Technician cannot complete session
        if ($user->hasRole('technician')) {
            abort(403, 'Kỹ thuật viên không có quyền chốt kỳ kiểm kê.');
        }

        // 2. Using dept can only complete their own department session
        if ($user->hasRole('using_dept')) {
            if (! $user->department_id || ! $this->session->department_id || $this->session->department_id !== $user->department_id) {
                abort(403, 'Khoa sử dụng không có quyền chốt kỳ kiểm kê của khoa khác.');
            }
        }

        // 3. Directorate has no permission
        if ($user->hasRole('directorate')) {
            abort(403, 'Ban Giám đốc không có quyền chốt kỳ kiểm kê.');
        }

        if ($this->session->isCompleted()) {
            return;
        }

        DB::transaction(function () {
            // Chuyển toàn bộ các mục chưa kiểm (result IS NULL) thành THIẾU
            InventoryItem::where('inventory_session_id', $this->session->id)
                ->whereNull('result')
                ->update([
                    'result' => InventoryItemResultEnum::THIEU->value,
                ]);

            $this->session->update([
                'status' => InventorySessionStatusEnum::COMPLETED,
                'completed_at' => now(),
            ]);
        });

        $this->session->refresh();
        session()->flash('message', 'Đã chốt kỳ kiểm kê thành công. Mọi thiết bị chưa kiểm đếm đã được tự động chuyển thành THIẾU.');
    }

    public function render()
    {
        $query = InventoryItem::with(['equipment.category', 'systemDepartment', 'systemLocation', 'checker'])
            ->where('inventory_session_id', $this->session->id);

        if ($this->searchItem) {
            $query->where(function ($q) {
                $q->where('scanned_code', 'like', '%'.$this->searchItem.'%')
                    ->orWhereHas('equipment', function ($eqQ) {
                        $eqQ->where('name', 'like', '%'.$this->searchItem.'%')
                            ->orWhere('serial_number', 'like', '%'.$this->searchItem.'%');
                    });
            });
        }

        if ($this->resultFilter) {
            if ($this->resultFilter === 'untested') {
                $query->whereNull('result');
            } else {
                $query->where('result', $this->resultFilter);
            }
        }

        $items = $query->orderByRaw('CASE WHEN result IS NULL THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->paginate(25);

        // Stats calculation
        $totalCount = $this->session->items()->count();
        $matchedCount = $this->session->items()->where('result', InventoryItemResultEnum::KHOP->value)->count();
        $missingCount = $this->session->items()->where('result', InventoryItemResultEnum::THIEU->value)->count();
        $excessCount = $this->session->items()->where('result', InventoryItemResultEnum::THUA->value)->count();
        $brokenCount = $this->session->items()->where('result', InventoryItemResultEnum::HONG->value)->count();
        $untestedCount = $this->session->items()->whereNull('result')->count();

        $user = auth()->user();
        $canComplete = ! $user->hasRole('technician') && ! $user->hasRole('directorate')
            && (! $user->hasRole('using_dept') || ($user->department_id && $this->session->department_id === $user->department_id));

        return view('livewire.inventory.inventory-show', [
            'items' => $items,
            'totalCount' => $totalCount,
            'matchedCount' => $matchedCount,
            'missingCount' => $missingCount,
            'excessCount' => $excessCount,
            'brokenCount' => $brokenCount,
            'untestedCount' => $untestedCount,
            'canComplete' => $canComplete,
        ])->layout('components.layouts.app', ['title' => 'Kiểm kê: '.$this->session->name.' - MEMS']);
    }
}
