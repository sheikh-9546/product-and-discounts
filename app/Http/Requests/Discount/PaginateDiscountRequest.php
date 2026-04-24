<?php

namespace App\Http\Requests\Discount;

use App\Http\Requests\JsonRequest;
use Illuminate\Validation\Rule;

class PaginateDiscountRequest extends JsonRequest
{
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string'],
            'page' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:200'],
            'sort_column' => ['sometimes', 'nullable', Rule::in(['created_at', 'name', 'type', 'value', 'is_active'])],
            'sort_direction' => ['sometimes', 'nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'search' => ['description' => 'Search discounts by name (LIKE).', 'example' => 'summer'],
            'page' => ['description' => 'Page number.', 'example' => 1],
            'per_page' => ['description' => 'Items per page (max 200).', 'example' => 25],
            'sort_column' => ['description' => 'Sort column.', 'example' => 'created_at'],
            'sort_direction' => ['description' => 'Sort direction.', 'example' => 'desc'],
        ];
    }

    public function getSearch(): ?string
    {
        return $this->get('search');
    }

    public function getPage(): int
    {
        return (int) $this->get('page', 1);
    }

    public function getPerPage(): int
    {
        return (int) $this->get('per_page', 50);
    }

    public function getSortColumn(): string
    {
        return (string) $this->get('sort_column', 'created_at');
    }

    public function getSortDirection(): string
    {
        return (string) $this->get('sort_direction', 'desc');
    }
}

