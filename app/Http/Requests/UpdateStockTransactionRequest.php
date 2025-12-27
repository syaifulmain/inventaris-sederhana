<?php

namespace App\Http\Requests;

use App\Enums\StockTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStockTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => 'sometimes|required|exists:products,id',
            'supplier_id' => 'sometimes|required|exists:suppliers,id',
            'type' => ['sometimes', 'required', Rule::enum(StockTransactionType::class)],
            'quantity' => 'sometimes|required|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'transaction_date' => 'sometimes|required|date',
        ];
    }
}
