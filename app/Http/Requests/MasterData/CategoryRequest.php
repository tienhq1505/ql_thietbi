<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master_data.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('category')?->id ?? $this->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('equipment_categories', 'code')->ignore($id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã loại thiết bị không được để trống.',
            'code.unique' => 'Mã loại thiết bị đã tồn tại trong hệ thống (kể cả bản ghi đã xóa).',
            'name.required' => 'Tên loại thiết bị không được để trống.',
        ];
    }
}
