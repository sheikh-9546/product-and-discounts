<?php

namespace App\Http\Requests\Product;

use App\Http\Requests\JsonRequest;
use Illuminate\Validation\Rule;

class PaginateProductRequest extends JsonRequest
{
    public function rules(): array
    {
        return [
            'search'                => ['sometimes', 'nullable', 'string'],
            'category_id'           => ['sometimes', 'nullable', 'integer'],
            'include_subcategories' => ['sometimes', 'nullable', 'boolean'],
            'page'                  => ['sometimes', 'nullable', 'integer', 'min:1'],
            'per_page'              => ['sometimes', 'nullable', 'integer', 'min:1', 'max:200'],
            'sort_column'           => ['sometimes', 'nullable', Rule::in(['created_at', 'name', 'price'])],
            'sort_direction'        => ['sometimes', 'nullable', Rule::in(['asc', 'desc'])],
        ];
    }

    public function queryParameters(): array
    {
        return [
            'search' => [
                'description' => 'LIKE-based search term (fallback) with optional MySQL full-text when available.',
                'example'     => 'shoe',
            ],
            'category_id' => [
                'description' => 'Filter products by category id.',
                'example'     => 1,
            ],
            'include_subcategories' => [
                'description' => 'If true, includes products in nested subcategories of category_id.',
                'example'     => true,
            ],
            'page' => [
                'description' => 'Page number.',
                'example'     => 1,
            ],
            'per_page' => [
                'description' => 'Items per page (max 200).',
                'example'     => 25,
            ],
            'sort_column' => [
                'description' => 'Sort column.',
                'example'     => 'created_at',
            ],
            'sort_direction' => [
                'description' => 'Sort direction.',
                'example'     => 'desc',
            ],
        ];
    }

    public function getSearch(): ?string
    {
        return $this->get('search');
    }

    public function getCategoryId(): ?int
    {
        $id = $this->get('category_id');

        return $id !== null ? (int) $id : null;
    }

    public function getIncludeSubcategories(): bool
    {
        return (bool) $this->boolean('include_subcategories', false);
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
