<?php

namespace App\Http\Resources\Category;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        $children = $this->whenLoaded('childrenRecursive', function () {
            return CategoryResource::collection($this->childrenRecursive);
        }, function () {
            return $this->whenLoaded('children', function () {
                return CategoryResource::collection($this->children);
            });
        });

        return [
            'id'        => $this->id,
            'parent_id' => $this->parent_id,
            'name'      => $this->name,
            'children'  => $children,
        ];
    }
}
