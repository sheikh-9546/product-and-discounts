<?php

namespace App\Http\Resources\Discount;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DiscountPaginateResourceCollection extends ResourceCollection
{
    private array $pagination;

    public function __construct($resource)
    {
        $this->toPaginate($resource);
        parent::__construct($resource->getCollection());
    }

    private function toPaginate($resource): void
    {
        $this->pagination = [
            'total' => $resource->total(),
            'count' => $resource->count(),
            'per_page' => $resource->perPage(),
            'current_page' => $resource->currentPage(),
            'total_pages' => $resource->lastPage(),
        ];
    }

    public function toArray($request): array
    {
        return [
            'records' => DiscountResource::collection($this->collection),
            'meta' => $this->pagination,
        ];
    }
}

