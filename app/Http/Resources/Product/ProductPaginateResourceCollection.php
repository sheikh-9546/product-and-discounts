<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductPaginateResourceCollection extends ResourceCollection
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
            'records' => ProductPaginateResource::collection($this->collection),
            'meta' => $this->pagination,
        ];
    }
}

