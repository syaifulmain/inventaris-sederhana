<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $supplierId = $this->route('id') ?? null;

        return [
            'code' => 'required|string|unique:suppliers,code,' . $supplierId,
            'name' => 'required|string',
            'address' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Kode supplier wajib diisi',
            'name.required' => 'Nama supplier wajib diisi',
            'address.required' => 'Alamat supplier wajib diisi',
            'code.unique' => 'Kode supplier sudah digunakan',
        ];
    }
}
