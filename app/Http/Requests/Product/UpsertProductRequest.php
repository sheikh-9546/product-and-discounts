<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\JsonRequest;

class UpsertProductRequest extends JsonRequest
{
    public function rules(): array
    {
        return [
            'category_id'    => ['sometimes', 'nullable', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'discount_ids'   => ['sometimes', 'array'],
            'discount_ids.*' => ['integer', 'exists:discounts,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'category_id' => [
                'description' => 'Category id for the product.',
                'example'     => 1,
            ],
            'name' => [
                'description' => 'Product name.',
                'example'     => 'Running Shoes',
            ],
            'description' => [
                'description' => 'Product description.',
                'example'     => 'Lightweight shoes for daily runs.',
            ],
            'price' => [
                'description' => 'Base price.',
                'example'     => 99.99,
            ],
            'discount_ids' => [
                'description' => 'Attach discounts directly to this product.',
                'example'     => [1, 2],
            ],
        ];
    }

    public function payload(): array
    {
        return $this->only(['category_id', 'name', 'description', 'price']);
    }

    public function getDiscountIds(): array
    {
        return $this->get('discount_ids', []);
    }
}
