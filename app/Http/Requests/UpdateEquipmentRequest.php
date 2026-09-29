<?php

namespace App\Http\Requests;

use App\Enums\EquipmentStatusEnum;
use App\Enums\OwnershipTypeEnum;
use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $equipment = $this->route('equipment');

        return $equipment ? $this->user()->can('update', $equipment) : false;
    }

    public function rules(): array
    {
        $equipmentId = $this->route('equipment')?->id;

        return [
            'code' => ['required', 'string', 'max:50', 'unique:equipment,code,'.$equipmentId],
            'name' => ['required', 'string', 'max:255'],
            'equipment_model_id' => ['required', 'exists:equipment_models,id'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'manufacturer_id' => ['required', 'exists:manufacturers,id'],
            'serial_number' => ['nullable', 'string', 'max:100', 'unique:equipment,serial_number,'.$equipmentId],
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
                function ($attribute, $value, $fail, $validator) {
                    $data = $validator->getData();
                    $deptId = $data['department_id'] ?? null;
                    if ($value && $deptId) {
                        $location = Location::find($value);
                        if ($location && (int) $location->department_id !== (int) $deptId) {
                            $fail('Vị trí được chọn không thuộc Khoa / Phòng đã chọn.');
                        }
                    }
                },
            ],
            'manager_id' => ['nullable', 'exists:users,id'],
            'manager_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(EquipmentStatusEnum::class)],
            // condition bị loại khỏi UpdateEquipmentRequest — chỉ đổi qua EquipmentShow::updateCondition
            'ownership_type' => ['nullable', Rule::enum(OwnershipTypeEnum::class)],
            'origin_model_text' => ['nullable', 'string'],
            'source_ref' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ];
    }
}
