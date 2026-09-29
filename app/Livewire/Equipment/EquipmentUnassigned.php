<?php

namespace App\Livewire\Equipment;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentDepartmentHistory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class EquipmentUnassigned extends Component
{
    use AuthorizesRequests, WithPagination;

    public string $search = '';

    public array $selectedEquipment = [];

    public bool $selectAll = false;

    public string $target_department_id = '';

    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $user = auth()->user();
        if (! $user || ! ($user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.edit'))) {
            abort(403, 'Bạn không có quyền truy cập trang gán khoa thiết bị chưa phân khoa.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectedEquipment = [];
        $this->selectAll = false;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $unassignedDept = Department::where('code', 'CHUA-PHAN-KHOA')->first();
            $query = Equipment::where('department_id', $unassignedDept?->id);

            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%')
                        ->orWhere('serial_number', 'like', '%'.$this->search.'%');
                });
            }

            $this->selectedEquipment = $query->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selectedEquipment = [];
        }
    }

    public function assignDepartment()
    {
        $user = auth()->user();
        if (! $user || ! ($user->hasRole('admin') || $user->hasRole('equipment_dept') || $user->can('equipment.edit'))) {
            abort(403, 'Bạn không có quyền thực hiện thao tác gán khoa.');
        }

        $this->validate([
            'target_department_id' => 'required|exists:departments,id',
            'selectedEquipment' => 'required|array|min:1',
        ], [
            'target_department_id.required' => 'Vui lòng chọn Khoa / Phòng cần gán.',
            'target_department_id.exists' => 'Khoa / Phòng được chọn không hợp lệ.',
            'selectedEquipment.required' => 'Vui lòng chọn ít nhất 1 thiết bị.',
            'selectedEquipment.min' => 'Vui lòng chọn ít nhất 1 thiết bị.',
        ]);

        $unassignedDept = Department::where('code', 'CHUA-PHAN-KHOA')->first();
        if ($unassignedDept && (int) $this->target_department_id === (int) $unassignedDept->id) {
            $this->addError('target_department_id', 'Vui lòng chọn một khoa phòng làm việc thực tế (khác Chưa phân khoa).');

            return;
        }

        $targetDept = Department::findOrFail($this->target_department_id);
        $items = Equipment::whereIn('id', $this->selectedEquipment)->get();

        $count = 0;
        DB::transaction(function () use ($items, $user, $targetDept, &$count) {
            foreach ($items as $equipment) {
                // Policy check for updating equipment
                if (! $user->can('update', $equipment)) {
                    continue;
                }

                $fromDeptId = $equipment->department_id;
                $equipment->update([
                    'department_id' => $targetDept->id,
                ]);

                EquipmentDepartmentHistory::create([
                    'equipment_id' => $equipment->id,
                    'from_department_id' => $fromDeptId,
                    'to_department_id' => $targetDept->id,
                    'assigned_by' => $user->id,
                    'note' => 'Gán khoa hàng loạt từ danh sách Chưa phân khoa',
                ]);

                $count++;
            }
        });

        $this->selectedEquipment = [];
        $this->selectAll = false;
        $this->resetPage();

        session()->flash('message', "Đã gán thành công khoa \"{$targetDept->name}\" cho {$count} thiết bị!");
    }

    public function render()
    {
        $unassignedDept = Department::where('code', 'CHUA-PHAN-KHOA')->first();
        $unassignedDeptId = $unassignedDept ? $unassignedDept->id : null;

        $query = Equipment::with(['category', 'manufacturer', 'equipmentModel'])
            ->where('department_id', $unassignedDeptId);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                    ->orWhereHas('equipmentModel', function ($mq) {
                        $mq->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        $equipmentList = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $departments = Department::where('code', '!=', 'CHUA-PHAN-KHOA')
            ->orderBy('name')
            ->get();

        return view('livewire.equipment.unassigned', [
            'equipmentList' => $equipmentList,
            'departments' => $departments,
        ])->layout('components.layouts.app', ['title' => 'Thiết bị Chưa phân khoa - MEMS']);
    }
}
