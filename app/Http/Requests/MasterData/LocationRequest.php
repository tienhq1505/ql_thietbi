<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master_data.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('location')?->id ?? $this->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('locations', 'code')->ignore($id)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'department_id' => ['required', 'exists:departments,id'],
            'building' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:50'],
            'room' => ['nullable', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã vị trí không được để trống.',
            'code.unique' => 'Mã vị trí đã tồn tại.',
            'name.required' => 'Tên vị trí không được để trống.',
            'department_id.required' => 'Vui lòng chọn Khoa / Phòng quản lý vị trí.',
        ];
    }
}
