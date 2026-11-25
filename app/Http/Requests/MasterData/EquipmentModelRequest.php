<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EquipmentModelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master_data.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('model')?->id ?? $this->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('equipment_models', 'code')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:equipment_categories,id'],
            'manufacturer_id' => ['required', 'exists:manufacturers,id'],
            'specs' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã Model không được để trống.',
            'code.unique' => 'Mã Model đã tồn tại trong hệ thống (kể cả bản ghi đã xóa).',
            'name.required' => 'Tên Model không được để trống.',
            'category_id.required' => 'Vui lòng chọn loại thiết bị.',
            'manufacturer_id.required' => 'Vui lòng chọn hãng sản xuất.',
        ];
    }
}
