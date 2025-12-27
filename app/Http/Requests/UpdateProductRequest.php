<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255',
            'code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('products')->ignore($productId)
            ],
            'category_id' => 'sometimes|integer|exists:categories,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Kode produk sudah digunakan',
            'code.max' => 'Kode produk maksimal 50 karakter',
            'category_id.exists' => 'Kategori tidak ditemukan',
            'name.max' => 'Nama produk maksimal 255 karakter',
        ];
    }
}
 