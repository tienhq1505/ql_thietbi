<?php

namespace App\Livewire\Equipment;

use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDepartmentHistory;
use App\Models\EquipmentModel;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class EquipmentEdit extends Component
{
    use AuthorizesRequests;

    public Equipment $equipment;

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

    public $funding_source = '';

    public $supplier_id = '';

    public $warranty_expiration_date = '';

    public $department_id = '';

    public $location_id = '';

    public $manager_id = '';

    public $manager_name = '';

    public $status = '';

    // condition bị loại khỏi form — chỉ cập nhật qua EquipmentShow::updateCondition
    public $ownership_type = 'mua_sam';

    public $origin_model_text = '';

    public $source_ref = '';

    public $note = '';

    public function mount($id)
    {
        $this->equipment = Equipment::findOrFail($id);
        $this->authorize('update', $this->equipment);

        $this->code = $this->equipment->code;
        $this->name = $this->equipment->name;
        $this->equipment_model_id = $this->equipment->equipment_model_id;
        $this->category_id = $this->equipment->category_id;
        $this->manufacturer_id = $this->equipment->manufacturer_id;
        $this->serial_number = $this->equipment->serial_number ?? '';
        $this->manufacture_year = $this->equipment->manufacture_year;
        $this->origin_country = $this->equipment->origin_country;
        $this->import_date = $this->equipment->import_date?->format('Y-m-d');
        $this->usage_start_date = $this->equipment->usage_start_date?->format('Y-m-d');
        $this->purchase_price = $this->equipment->purchase_price;
        $this->funding_source = $this->equipment->funding_source;
        $this->supplier_id = $this->equipment->supplier_id;
        $this->warranty_expiration_date = $this->equipment->warranty_expiration_date?->format('Y-m-d');
        $this->department_id = $this->equipment->department_id;
        $this->location_id = $this->equipment->location_id;
        $this->manager_id = $this->equipment->manager_id;
        $this->manager_name = $this->equipment->manager_name;
        $this->status = $this->equipment->status?->value ?? EquipmentStatusEnum::DANG_SU_DUNG->value;
        // condition KHÔNG load vào form — đổi qua EquipmentShow::updateCondition
        $this->ownership_type = $this->equipment->ownership_type?->value ?? OwnershipTypeEnum::MUA_SAM->value;
        $this->origin_model_text = $this->equipment->origin_model_text;
        $this->source_ref = $this->equipment->source_ref;
        $this->note = $this->equipment->note;
    }

    public function save()
    {
        $rules = [
            'code' => ['required', 'string', 'max:50', Rule::unique('equipment', 'code')->ignore($this->equipment->id)],
            'name' => ['required', 'string', 'max:255'],
            'equipment_model_id' => ['required', 'exists:equipment_models,id'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'manufacturer_id' => ['required', 'exists:manufacturers,id'],
            'serial_number' => ['nullable', 'string', 'max:100', Rule::unique('equipment', 'serial_number')->ignore($this->equipment->id)],
            'manufacture_year' => ['nullable', 'integer', 'min:1970', 'max:'.date('Y')],
            'origin_country' => ['nullable', 'string', 'max:100'],
            'import_date' => ['nullable', 'date'],
            'usage_start_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'funding_source' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'warranty_expiration_date' => ['nullable', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'location_id' => [
                'nullable',
                'exists:locations,id',
                function ($attribute, $value, $fail) {
                    if ($value && $this->department_id) {
                        $location = Location::find($value);
                        if ($location && (int) $location->department_id !== (int) $this->department_id) {
                            $fail('Vị trí được chọn không thuộc Khoa / Phòng đã chọn.');
                        }
                    }
                },
            ],
            'manager_id' => ['nullable', 'exists:users,id'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EquipmentStatusEnum::class)],
            'ownership_type' => ['nullable', Rule::enum(OwnershipTypeEnum::class)],
            'origin_model_text' => ['nullable', 'string'],
            'source_ref' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ];

        $validatedData = $this->validate($rules);

        $serial = trim((string) ($validatedData['serial_number'] ?? ''));
        if ($serial === '') {
            $validatedData['serial_number'] = null;
            $validatedData['serial_pending'] = true;
        } else {
            $validatedData['serial_number'] = $serial;
            $validatedData['serial_pending'] = false;
        }

        $validatedData['location_id'] = ! empty($validatedData['location_id']) ? (int) $validatedData['location_id'] : null;
        $validatedData['supplier_id'] = ! empty($validatedData['supplier_id']) ? (int) $validatedData['supplier_id'] : null;
        $validatedData['manager_id'] = ! empty($validatedData['manager_id']) ? (int) $validatedData['manager_id'] : null;

        $validatedData['ownership_type'] = $validatedData['ownership_type'] ?? 'mua_sam';

        $oldDepartmentId = $this->equipment->department_id;
        $newDepartmentId = (int) $validatedData['department_id'];

        $this->equipment->update($validatedData);

        if ($oldDepartmentId && $newDepartmentId && (int) $oldDepartmentId !== (int) $newDepartmentId) {
            EquipmentDepartmentHistory::create([
                'equipment_id' => $this->equipment->id,
                'from_department_id' => $oldDepartmentId,
                'to_department_id' => $newDepartmentId,
                'assigned_by' => auth()->id(),
                'note' => 'Điều chuyển qua cập nhật hồ sơ thiết bị',
            ]);
        }

        session()->flash('message', 'Cập nhật thông tin thiết bị thành công!');

        return redirect()->route('equipment.show', $this->equipment->id);
    }

    public function render()
    {
        return view('livewire.equipment.edit', [
            'models' => EquipmentModel::with(['category', 'manufacturer'])->orderBy('name')->get(),
            'categories' => EquipmentCategory::orderBy('name')->get(),
            'manufacturers' => Manufacturer::orderBy('name')->get(),
            'suppliers' => Supplier::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
            'locations' => $this->department_id ? Location::where('department_id', $this->department_id)->get() : Location::all(),
            'users' => User::orderBy('name')->get(),
            'statuses' => EquipmentStatusEnum::cases(),
            'ownershipTypes' => OwnershipTypeEnum::cases(),
        ])->layout('components.layouts.app', ['title' => 'Chỉnh sửa Thiết bị: '.$this->equipment->code]);
    }
}
