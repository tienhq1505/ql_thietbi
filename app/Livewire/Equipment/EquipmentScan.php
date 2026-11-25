<?php

namespace App\Livewire\Equipment;

use App\Enums\DocumentTypeEnum;
use App\Enums\EquipmentConditionEnum;
use App\Models\Equipment;
use App\Services\EquipmentConditionService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.mobile')]
#[Title('Thông tin thiết bị - Quét QR')]
class EquipmentScan extends Component
{
    use AuthorizesRequests;

    public Equipment $equipment;

    // Condition update modal state
    public bool $showConditionModal = false;

    public string $newCondition = '';

    public string $conditionReason = '';

    public string $conditionNote = '';

    public function mount(string $code): void
    {
        $code = trim($code);
        $decodedCode = rawurldecode($code);

        $equipment = Equipment::where('code', $code)
            ->orWhere('code', $decodedCode)
            ->first();

        if (! $equipment) {
            abort(404, 'Không tìm thấy thiết bị với mã này.');
        }

        if (! auth()->user()->can('view', $equipment)) {
            abort(403, 'Bạn không có quyền xem thông tin thiết bị này.');
        }

        $this->equipment = $equipment->load([
            'department',
            'location',
            'category',
            'manufacturer',
            'equipmentModel',
            'supplier',
            'conditionHistories' => fn ($q) => $q->with('checkedBy')->take(5),
            'documents',
        ]);

        $this->newCondition = $this->equipment->condition?->value ?? '';
    }

    public function openConditionModal(): void
    {
        $this->authorize('updateCondition', $this->equipment);

        $this->resetValidation();
        $this->newCondition = $this->equipment->condition?->value ?? '';
        $this->conditionReason = '';
        $this->conditionNote = '';
        $this->showConditionModal = true;
    }

    public function closeConditionModal(): void
    {
        $this->showConditionModal = false;
        $this->resetValidation();
    }

    public function saveCondition(EquipmentConditionService $conditionService): void
    {
        $this->authorize('updateCondition', $this->equipment);

        $this->validate([
            'newCondition' => 'required|in:'.implode(',', array_column(EquipmentConditionEnum::cases(), 'value')),
            'conditionReason' => 'required|string|max:255',
            'conditionNote' => 'nullable|string|max:1000',
        ], [
            'newCondition.required' => 'Vui lòng chọn tình trạng mới.',
            'conditionReason.required' => 'Vui lòng nhập lý do thay đổi tình trạng.',
        ]);

        $oldCondition = $this->equipment->condition instanceof EquipmentConditionEnum
            ? $this->equipment->condition->value
            : $this->equipment->condition;

        if ($oldCondition === $this->newCondition) {
            $this->addError('newCondition', 'Tình trạng mới phải khác tình trạng hiện tại.');

            return;
        }

        $conditionService->updateCondition(
            $this->equipment,
            $this->newCondition,
            $this->conditionReason,
            $this->conditionNote,
            auth()->user()
        );

        $this->equipment->refresh();
        $this->equipment->load([
            'conditionHistories' => fn ($q) => $q->with('checkedBy')->take(5),
        ]);

        $this->showConditionModal = false;
        session()->flash('message', 'Đã cập nhật tình trạng kỹ thuật và ghi nhận lịch sử thành công.');
    }

    public function render()
    {
        $allDocTypes = DocumentTypeEnum::cases();
        $existingDocs = $this->equipment->documents->keyBy(function ($doc) {
            return $doc->document_type instanceof DocumentTypeEnum
                ? $doc->document_type->value
                : (string) $doc->document_type;
        });

        return view('livewire.equipment.equipment-scan', [
            'allDocTypes' => $allDocTypes,
            'existingDocs' => $existingDocs,
            'conditions' => EquipmentConditionEnum::cases(),
        ]);
    }
}
