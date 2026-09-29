<?php

namespace App\Http\Requests\MasterData;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManufacturerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('master_data.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('manufacturer')?->id ?? $this->id;

        return [
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('manufacturers', 'code')->ignore($id)->whereNull('deleted_at'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:100'],
            'website' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã hãng sản xuất không được để trống.',
            'code.unique' => 'Mã hãng sản xuất đã tồn tại.',
            'name.required' => 'Tên hãng sản xuất không được để trống.',
        ];
    }
}
