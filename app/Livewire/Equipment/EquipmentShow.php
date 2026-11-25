<?php

namespace App\Livewire\Equipment;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\EquipmentConditionEnum;
use App\Models\Equipment;
use App\Models\EquipmentDocument;
use App\Services\EquipmentConditionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EquipmentShow extends Component
{
    use AuthorizesRequests;

    public Equipment $equipment;

    public string $activeTab = 'overview'; // overview | documents

    public string $newCondition = '';

    public string $conditionReason = '';

    public string $conditionNote = '';

    // Document edit modal state
    public bool $showDocModal = false;

    public ?string $editingDocType = null;

    public string $docStatus = 'chua_ra_soat';

    public ?string $docValidUntil = null;

    public ?string $docNote = null;

    public function mount($id)
    {
        $this->equipment = Equipment::with(['category', 'manufacturer', 'equipmentModel', 'supplier', 'department', 'location', 'manager', 'deleteRequestedBy', 'conditionHistories.checkedBy', 'documents'])->findOrFail($id);
        $this->authorize('view', $this->equipment);
        $this->newCondition = $this->equipment->condition?->value ?? EquipmentConditionEnum::DANG_HOAT_DONG->value;
    }

    public function requestDelete()
    {
        $this->authorize('requestDelete', $this->equipment);

        $this->equipment->update([
            'delete_requested_at' => now(),
            'delete_requested_by' => auth()->id(),
        ]);

        session()->flash('message', 'Đã gửi đề nghị xóa thiết bị đến Quản trị hệ thống.');
    }

    public function cancelDeleteRequest()
    {
        $this->authorize('delete', $this->equipment);

        $this->equipment->update([
            'delete_requested_at' => null,
            'delete_requested_by' => null,
        ]);

        session()->flash('message', 'Đã hủy đề nghị xóa thiết bị.');
    }

    public function softDelete()
    {
        $this->authorize('delete', $this->equipment);

        $this->equipment->update([
            'deleted_by' => auth()->id(),
        ]);

        $this->equipment->delete();

        session()->flash('message', 'Đã xóa thiết bị thành công (Soft Delete).');

        return redirect()->route('equipment.index');
    }

    public function updateCondition(EquipmentConditionService $conditionService)
    {
        $this->authorize('updateCondition', $this->equipment);

        $this->validate([
            'newCondition' => 'required|in:'.implode(',', array_column(EquipmentConditionEnum::cases(), 'value')),
            'conditionReason' => 'required|string|max:255',
            'conditionNote' => 'nullable|string|max:1000',
        ], [
            'conditionReason.required' => 'Vui lòng nhập lý do thay đổi tình trạng.',
        ]);

        $oldCondition = $this->equipment->condition?->value;

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
        $this->equipment->load('conditionHistories.checkedBy');
        $this->reset(['conditionReason', 'conditionNote']);

        session()->flash('message', 'Đã cập nhật tình trạng kỹ thuật và lưu lịch sử thành công.');
    }

    public function openEditDocument(string $docTypeVal)
    {
        $this->authorize('update', $this->equipment);

        $this->editingDocType = $docTypeVal;
        $doc = $this->equipment->documents->firstWhere('document_type.value', $docTypeVal)
            ?? $this->equipment->documents->firstWhere('document_type', $docTypeVal);

        if ($doc) {
            $this->docStatus = $doc->status?->value ?? DocumentStatusEnum::CHUA_RA_SOAT->value;
            $this->docValidUntil = $doc->valid_until?->format('Y-m-d');
            $this->docNote = $doc->note;
        } else {
            $this->docStatus = DocumentStatusEnum::CHUA_RA_SOAT->value;
            $this->docValidUntil = null;
            $this->docNote = null;
        }

        $this->showDocModal = true;
    }

    public function saveDocument()
    {
        $this->authorize('update', $this->equipment);

        $this->validate([
            'editingDocType' => ['required', Rule::enum(DocumentTypeEnum::class)],
            'docStatus' => ['required', Rule::enum(DocumentStatusEnum::class)],
            'docValidUntil' => 'nullable|date',
            'docNote' => 'nullable|string|max:1000',
        ], [
            'editingDocType.required' => 'Vui lòng chọn loại hồ sơ giấy tờ.',
            'editingDocType.Illuminate\Validation\Rules\Enum' => 'Loại hồ sơ giấy tờ không hợp lệ.',
            'docStatus.required' => 'Vui lòng chọn trạng thái hồ sơ.',
        ]);

        EquipmentDocument::updateOrCreate(
            [
                'equipment_id' => $this->equipment->id,
                'document_type' => $this->editingDocType,
            ],
            [
                'status' => $this->docStatus,
                'valid_until' => $this->docValidUntil ?: null,
                'note' => $this->docNote ?: null,
            ]
        );

        $this->equipment->refresh();
        $this->equipment->load('documents');
        $this->showDocModal = false;
        $this->editingDocType = null;

        session()->flash('message', 'Cập nhật trạng thái hồ sơ thiết bị thành công.');
    }

    public function exportPdf()
    {
        $this->authorize('export', Equipment::class);

        $pdf = Pdf::loadView('pdf.equipment_profile', [
            'equipment' => $this->equipment,
        ]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'ho-so-thiet-bi-'.$this->equipment->code.'.pdf'
        );
    }

    public function render()
    {
        $allDocTypes = DocumentTypeEnum::cases();
        $docMap = [];
        foreach ($this->equipment->documents as $doc) {
            $typeVal = is_string($doc->document_type) ? $doc->document_type : $doc->document_type->value;
            $docMap[$typeVal] = $doc;
        }

        return view('livewire.equipment.show', [
            'conditions' => EquipmentConditionEnum::cases(),
            'allDocTypes' => $allDocTypes,
            'docMap' => $docMap,
            'docStatuses' => DocumentStatusEnum::cases(),
        ])->layout('components.layouts.app', ['title' => 'Chi tiết Thiết bị: '.$this->equipment->code]);
    }
}
