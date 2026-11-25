<?php

namespace App\Livewire\Inventory;

use App\Enums\EquipmentStatusEnum;
use App\Enums\InventorySessionStatusEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\InventoryItem;
use App\Models\InventorySession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public ?string $statusFilter = '';

    public ?int $departmentFilter = null;

    // Modal state
    public bool $showCreateModal = false;

    public string $name = '';

    public ?int $department_id = null;

    public string $start_date = '';

    public string $end_date = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }

    protected $messages = [
        'name.required' => 'Vui lòng nhập tên kỳ kiểm kê.',
        'start_date.required' => 'Vui lòng chọn ngày bắt đầu.',
        'end_date.required' => 'Vui lòng chọn ngày kết thúc.',
        'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
    ];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user || $user->hasRole('directorate') || ! $user->can('inventory.manage')) {
            abort(403, 'Bạn không có quyền truy cập tính năng Kiểm kê.');
        }

        if ($user->hasRole('using_dept') && ! $user->department_id) {
            abort(403, 'Tài khoản khoa sử dụng chưa được gán khoa.');
        }

        // Set default dates
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(7)->format('Y-m-d');

        if ($user->hasRole('using_dept')) {
            $this->department_id = $user->department_id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $user = auth()->user();

        if ($user->hasRole('technician')) {
            abort(403, 'Kỹ thuật viên chỉ có quyền quét kiểm kê trong kỳ đang mở, không được tạo kỳ.');
        }

        if ($user->hasRole('using_dept') && ! $user->department_id) {
            abort(403, 'Tài khoản khoa sử dụng chưa được gán khoa.');
        }

        $this->resetValidation();
        $this->name = 'Kỳ kiểm kê thiết bị T'.now()->format('m/Y');
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(7)->format('Y-m-d');

        if ($user->hasRole('using_dept')) {
            $this->department_id = $user->department_id;
        } else {
            $this->department_id = null;
        }

        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createSession(): void
    {
        $user = auth()->user();

        if ($user->hasRole('technician')) {
            abort(403, 'Kỹ thuật viên không có quyền tạo kỳ kiểm kê.');
        }

        if ($user->hasRole('using_dept')) {
            if (! $user->department_id) {
                abort(403, 'Tài khoản khoa sử dụng chưa được gán khoa.');
            }
            $this->department_id = $user->department_id;
        }

        $this->validate();

        if ($user->hasRole('using_dept') && (! $this->department_id || $this->department_id !== $user->department_id)) {
            abort(403, 'Khoa sử dụng chỉ được tạo kỳ kiểm kê cho khoa của mình.');
        }

        $deptId = $this->department_id;

        $session = DB::transaction(function () use ($deptId, $user) {
            $newSession = InventorySession::create([
                'name' => $this->name,
                'department_id' => $deptId,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => InventorySessionStatusEnum::IN_PROGRESS,
                'created_by' => $user->id,
            ]);

            // Snapshot: Loại trừ thiết bị có trạng thái đã thanh lý
            $query = Equipment::query()
                ->where('status', '!=', EquipmentStatusEnum::DA_THANH_LY->value);

            if ($deptId) {
                $query->where('department_id', $deptId);
            }

            $equipments = $query->get(['id', 'code', 'department_id', 'location_id']);

            $items = [];
            $now = now();
            foreach ($equipments as $eq) {
                $items[] = [
                    'inventory_session_id' => $newSession->id,
                    'equipment_id' => $eq->id,
                    'scanned_code' => $eq->code,
                    'system_department_id' => $eq->department_id,
                    'system_location_id' => $eq->location_id,
                    'actual_location' => null,
                    'result' => null,
                    'note' => null,
                    'checked_by' => null,
                    'checked_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (! empty($items)) {
                InventoryItem::insert($items);
            }

            return $newSession;
        });

        session()->flash('message', 'Đã khởi tạo kỳ kiểm kê "'.$session->name.'" với '.$session->items()->count().' thiết bị thành công.');

        $this->showCreateModal = false;

        $this->redirect(route('inventory.show', $session->id), navigate: true);
    }

    public function render()
    {
        $user = auth()->user();

        $query = InventorySession::with(['department', 'creator'])
            ->withCount([
                'items as total_items',
                'items as checked_items' => function ($q) {
                    $q->whereNotNull('result');
                },
                'items as matched_items' => function ($q) {
                    $q->where('result', 'khop');
                },
                'items as missing_items' => function ($q) {
                    $q->where('result', 'thieu');
                },
                'items as excess_items' => function ($q) {
                    $q->where('result', 'thua');
                },
                'items as broken_items' => function ($q) {
                    $q->where('result', 'hong');
                },
            ])
            ->orderByDesc('id');

        // Scope by user role
        if ($user->hasRole('using_dept')) {
            if (! $user->department_id) {
                abort(403, 'Tài khoản khoa sử dụng chưa được gán khoa.');
            }
            $query->where('department_id', $user->department_id);
        } elseif ($this->departmentFilter) {
            $query->where('department_id', $this->departmentFilter);
        }

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $sessions = $query->paginate(15);

        $departments = Department::where('code', '!=', 'CHUA-PHAN-KHOA')->orderBy('name')->get();

        $canCreate = ! $user->hasRole('technician');

        return view('livewire.inventory.inventory-index', [
            'sessions' => $sessions,
            'departments' => $departments,
            'canCreate' => $canCreate,
        ])->layout('components.layouts.app', ['title' => 'Quản lý Kiểm kê - MEMS']);
    }
}
