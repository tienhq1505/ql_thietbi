<?php

namespace App\Livewire\Equipment;

use App\Enums\DocumentStatusEnum;
use App\Enums\DocumentTypeEnum;
use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Exports\EquipmentExport;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\Manufacturer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Excel;

class EquipmentIndex extends Component
{
    use AuthorizesRequests, WithPagination;

    public $search = '';

    public $department_id = '';

    public $category_id = '';

    public $manufacturer_id = '';

    public $status = '';

    public $condition = '';

    public $missing_doc_type = '';

    public $missing_serial = '';

    public $ownership_type = '';

    public $sortField = 'created_at';

    public $sortDirection = 'desc';

    public $perPage = 15;

    /** Các cột được phép sắp xếp (whitelist) */
    private const ALLOWED_SORT_FIELDS = [
        'code', 'name', 'status', 'condition', 'created_at',
        'purchase_price', 'manufacture_year', 'import_date',
    ];

    /** Giá trị perPage hợp lệ */
    private const ALLOWED_PER_PAGES = [15, 30, 50];

    protected $queryString = [
        'search' => ['except' => ''],
        'department_id' => ['except' => ''],
        'category_id' => ['except' => ''],
        'manufacturer_id' => ['except' => ''],
        'status' => ['except' => ''],
        'condition' => ['except' => ''],
        'missing_doc_type' => ['except' => ''],
        'missing_serial' => ['except' => ''],
        'ownership_type' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDepartmentId()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingManufacturerId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingCondition()
    {
        $this->resetPage();
    }

    public function updatingMissingDocType()
    {
        $this->resetPage();
    }

    public function updatingMissingSerial()
    {
        $this->resetPage();
    }

    public function updatingOwnershipType()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if (! in_array($field, self::ALLOWED_SORT_FIELDS, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function resetFilters()
    {
        $this->reset(['search', 'department_id', 'category_id', 'manufacturer_id', 'status', 'condition', 'missing_doc_type', 'missing_serial', 'ownership_type']);
        $this->resetPage();
    }

    public function buildFilteredQuery()
    {
        $query = Equipment::with(['category', 'manufacturer', 'equipmentModel', 'department', 'location', 'supplier', 'manager', 'documents']);

        // CRITICAL DEPT SCOPING FOR USING_DEPT / KHOA ROLE
        $user = auth()->user();
        if ($user->hasRole('using_dept') || ($user->can('equipment.view_department') && ! $user->can('equipment.view'))) {
            if ($user->department_id) {
                $query->where('department_id', $user->department_id);
            }
        } elseif ($this->department_id) {
            $query->where('department_id', $this->department_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('code', 'like', '%'.$this->search.'%')
                    ->orWhere('name', 'like', '%'.$this->search.'%')
                    ->orWhere('serial_number', 'like', '%'.$this->search.'%')
                    ->orWhere('manager_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('equipmentModel', function ($mq) {
                        $mq->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('manufacturer', function ($mq) {
                        $mq->where('name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('department', function ($dq) {
                        $dq->where('name', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        if ($this->manufacturer_id) {
            $query->where('manufacturer_id', $this->manufacturer_id);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->condition) {
            $query->where('condition', $this->condition);
        }

        if ($this->missing_doc_type) {
            $query->whereDoesntHave('documents', function ($dq) {
                $dq->where('document_type', $this->missing_doc_type)
                    ->where('status', DocumentStatusEnum::CO->value);
            });
        }

        if ($this->missing_serial === '1') {
            $query->where(function ($sq) {
                $sq->where('serial_pending', true)
                    ->orWhereNull('serial_number')
                    ->orWhere('serial_number', '');
            });
        }

        if ($this->ownership_type) {
            $query->where('ownership_type', $this->ownership_type);
        }

        $sortField = in_array($this->sortField, self::ALLOWED_SORT_FIELDS, true)
            ? $this->sortField
            : 'created_at';
        $sortDirection = in_array($this->sortDirection, ['asc', 'desc'], true)
            ? $this->sortDirection
            : 'desc';

        return $query->orderBy($sortField, $sortDirection);
    }

    public function exportExcel()
    {
        $this->authorize('export', Equipment::class);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new EquipmentExport($this->buildFilteredQuery()),
            'danh-sach-thiet-bi-'.date('Y-m-d').'.xlsx'
        );
    }

    public function exportCsv()
    {
        $this->authorize('export', Equipment::class);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new EquipmentExport($this->buildFilteredQuery()),
            'danh-sach-thiet-bi-'.date('Y-m-d').'.csv',
            Excel::CSV,
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }

    public function exportPdf()
    {
        $this->authorize('export', Equipment::class);

        $query = $this->buildFilteredQuery();
        $total = $query->count();

        if ($total > 200) {
            session()->flash('error', "Không thể xuất PDF danh sách quá 200 thiết bị (hiện có {$total} kết quả). Vui lòng dùng bộ lọc hoặc chọn xuất Excel/CSV.");

            return;
        }

        $items = $query->get();
        $pdf = Pdf::loadView('pdf.equipment_list', ['items' => $items]);

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            'danh-sach-thiet-bi-'.date('Y-m-d').'.pdf'
        );
    }

    public function render()
    {
        $this->authorize('viewAny', Equipment::class);

        $perPage = in_array((int) $this->perPage, self::ALLOWED_PER_PAGES, true)
            ? (int) $this->perPage
            : 15;

        $equipment = $this->buildFilteredQuery()->paginate($perPage);

        return view('livewire.equipment.index', [
            'equipmentList' => $equipment,
            'departments' => Department::orderBy('name')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'conditions' => EquipmentConditionEnum::cases(),
            'statuses' => EquipmentStatusEnum::cases(),
            'docTypes' => DocumentTypeEnum::cases(),
            'ownershipTypes' => OwnershipTypeEnum::cases(),
        ])->layout('components.layouts.app', ['title' => 'Danh sách Thiết bị Y tế - MEMS']);
    }
}
