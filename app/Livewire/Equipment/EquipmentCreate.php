<?php

namespace App\Livewire\Equipment;

use App\Enums\EquipmentConditionEnum;
use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class EquipmentCreate extends Component
{
    use AuthorizesRequests;

    public $code = '';

    public $name = '';

    public $equipment_model_id = '';

    public $category_id = '';

    public $manufacturer_id = '';

    public $serial_number = '';

    public $manufacture_year = '';

    public $origin_country = '';

    public $import_date = '';

    public $usage_start_date = '';

    public $purchase_price = 0;

    public $funding_source = 'Ngân sách Nhà nước';

    public $supplier_id = '';

    public $warranty_expiration_date = '';

    public $department_id = '';

    public $location_id = '';

    public $manager_id = '';

    public $manager_name = '';

    public $status = 'dang_su_dung';

    public $condition = 'dang_hoat_dong';

    public $ownership_type = 'mua_sam';

    public $origin_model_text = '';

    public $source_ref = '';

    public $note = '';

    public function mount()
    {
        $this->authorize('create', Equipment::class);
        $this->code = $this->generateNextCode();
        $this->manufacture_year = date('Y');
        $this->import_date = date('Y-m-d');
        $this->usage_start_date = date('Y-m-d');
    }

    /**
     * Sinh mã thiết bị theo dạng TB-YYYY-NNNN dựa trên số thứ tự tăng dần.
     * Tìm mã lớn nhất có dạng TB-{năm}-\d+ trong DB, +1.
     */
    private function generateNextCode(): string
    {
        $year = date('Y');
        $prefix = "TB-{$year}-";

        $last = Equipment::withTrashed()
            ->where('code', 'like', $prefix.'%')
            ->orderByRaw('LENGTH(code) DESC, code DESC')
            ->value('code');

        $next = 1;
        if ($last && preg_match('/TB-\d{4}-(\d+)$/', $last, $m)) {
            $next = (int) $m[1] + 1;
        }

        return $prefix.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function updatedEquipmentModelId($value)
    {
        if ($value) {
            $model = EquipmentModel::find($value);
            if ($model) {
                $this->category_id = $model->category_id;
                $this->manufacturer_id = $model->manufacturer_id;
                if (empty($this->name)) {
                    $this->name = $model->name;
                }
            }
        }
    }

    public function save()
    {
        $request = new StoreEquipmentRequest;
        $validatedData = $this->validate($request->rules());

        $serial = trim((string) ($validatedData['serial_number'] ?? ''));
        if ($serial === '') {
            $validatedData['serial_number'] = null;
            $validatedData['serial_pending'] = true;
        } else {
            $validatedData['serial_number'] = $serial;
            $validatedData['serial_pending'] = false;
        }

        $validatedData['ownership_type'] = $validatedData['ownership_type'] ?? 'mua_sam';

        Equipment::create($validatedData);

        session()->flash('message', 'Thêm mới thiết bị thành công!');

        return redirect()->route('equipment.index');
    }

    public function render()
    {
        return view('livewire.equipment.create', [
            'models' => EquipmentModel::with(['category', 'manufacturer'])->orderBy('name')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'locations' => $this->department_id ? Location::where('department_id', $this->department_id)->get() : Location::all(),
            'users' => User::orderBy('name')->get(),
            'statuses' => EquipmentStatusEnum::cases(),
            'conditions' => EquipmentConditionEnum::cases(),
            'ownershipTypes' => OwnershipTypeEnum::cases(),
        ])->layout('components.layouts.app', ['title' => 'Thêm Thiết bị Mới - MEMS']);
    }
}
