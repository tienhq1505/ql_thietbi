<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master_data.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('supplier')?->id ?? $this->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($id)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã nhà cung cấp không được để trống.',
            'code.unique' => 'Mã nhà cung cấp đã tồn tại.',
            'name.required' => 'Tên nhà cung cấp không được để trống.',
        ];
    }
}
