<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\JsonRequest;
use Illuminate\Validation\Rule;

class UpsertDiscountRequest extends JsonRequest
{
    public function rules(): array
    {
        return [
            'title'         => ['required', 'string', 'max:255'],
            'type'          => ['required', Rule::in(['percentage', 'fixed'])],
            'value'         => ['required', 'numeric', 'min:0'],
            'product_ids'   => ['sometimes', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'title'       => ['description' => 'Discount title.', 'example' => 'Summer Sale'],
            'type'        => ['description' => 'Discount type.', 'example' => 'percentage'],
            'value'       => ['description' => 'Percentage (0-100+) or fixed amount.', 'example' => 15],
            'product_ids' => ['description' => 'Attach directly to products.', 'example' => [10, 11]],
        ];
    }

    public function payload(): array
    {
        return $this->only(['title', 'type', 'value']);
    }

    public function getProductIds(): array
    {
        return $this->get('product_ids', []);
    }
}
