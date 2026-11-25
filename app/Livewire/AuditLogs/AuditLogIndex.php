<?php

namespace App\Livewire\AuditLogs;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentConditionHistory;
use App\Models\EquipmentDepartmentHistory;
use App\Models\EquipmentDocument;
use App\Models\EquipmentModel;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class AuditLogIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public ?int $user_id = null;

    public ?string $action = '';

    public ?string $auditable_type = '';

    public ?string $date_from = '';

    public ?string $date_to = '';

    public ?AuditLog $selectedLog = null;

    public bool $showModal = false;

    public function mount(): void
    {
        abort_unless(auth()->user()->can('audit_log.view'), 403, 'Bạn không có quyền xem nhật ký hệ thống (Audit Log).');
    }

    public function updatedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedAction(): void
    {
        $this->resetPage();
    }

    public function updatedAuditableType(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['user_id', 'action', 'auditable_type', 'date_from', 'date_to']);
        $this->resetPage();
    }

    public function viewDetails(int $logId): void
    {
        abort_unless(auth()->user()->can('audit_log.view'), 403);
        $this->selectedLog = AuditLog::with('user')->findOrFail($logId);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedLog = null;
    }

    public function render()
    {
        abort_unless(auth()->user()->can('audit_log.view'), 403);

        $query = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        if ($this->action) {
            $query->where('action', $this->action);
        }

        if ($this->auditable_type) {
            $query->where('auditable_type', $this->auditable_type);
        }

        if ($this->date_from) {
            $query->whereDate('created_at', '>=', $this->date_from);
        }

        if ($this->date_to) {
            $query->whereDate('created_at', '<=', $this->date_to);
        }

        $logs = $query->paginate(20);

        $users = User::orderBy('name')->get(['id', 'name']);

        $availableTypes = [
            Equipment::class => 'Thiết bị',
            EquipmentDocument::class => 'Hồ sơ thiết bị',
            EquipmentConditionHistory::class => 'Lịch sử tình trạng',
            EquipmentDepartmentHistory::class => 'Lịch sử chuyển khoa',
            User::class => 'Người dùng',
            Department::class => 'Khoa / Phòng',
            EquipmentCategory::class => 'Loại thiết bị',
            Manufacturer::class => 'Hãng sản xuất',
            EquipmentModel::class => 'Model thiết bị',
            Supplier::class => 'Nhà cung cấp',
            Location::class => 'Vị trí',
            InventorySession::class => 'Kỳ kiểm kê',
            InventoryItem::class => 'Mục kiểm kê',
        ];

        return view('livewire.audit-logs.index', [
            'logs' => $logs,
            'users' => $users,
            'availableTypes' => $availableTypes,
        ])->layout('components.layouts.app', ['title' => 'Nhật ký hệ thống (Audit Log) - MEMS']);
    }
}
