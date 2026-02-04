<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchProductsRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'string', 'max:255'],
            'min_price' => ['sometimes', 'numeric', 'min:0'],
            'max_price' => ['sometimes', 'numeric', 'min:0', 'gte:min_price'],
            'stock_status' => ['sometimes', 'in:in_stock,out_of_stock,low_stock'],
            'sort_by' => ['sometimes', 'in:name,price,stock,created_at'],
            'sort_order' => ['sometimes', 'in:asc,desc'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:50']
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.string' => 'Search query must be a string.',
            'q.max' => 'Search query must not exceed 255 characters.',
            'min_price.numeric' => 'Minimum price must be a number.',
            'min_price.min' => 'Minimum price cannot be negative.',
            'max_price.numeric' => 'Maximum price must be a number.',
            'max_price.min' => 'Maximum price cannot be negative.',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            'stock_status.in' => 'Stock status must be one of: in_stock, out_of_stock, low_stock.',
            'sort_by.in' => 'Sort by must be one of: name, price, stock, created_at.',
            'sort_order.in' => 'Sort order must be either asc or desc.',
            'per_page.integer' => 'Per page must be an integer.',
            'per_page.min' => 'Per page must be at least 1.',
            'per_page.max' => 'Per page cannot exceed 50.'
        ];
    }
}
