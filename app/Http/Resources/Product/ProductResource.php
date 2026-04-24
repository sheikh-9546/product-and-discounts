<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\Category\CategoryResource;
use App\Services\Pricing\DiscountCalculator;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var DiscountCalculator $calculator */
        $calculator = app(DiscountCalculator::class);
        $pricing = $calculator->priceWithBestDiscount($this->resource);

        return [
            'id' => $this->id,
            'category' => $this->category ? CategoryResource::make($this->category) : null,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $pricing['original_price'],
            'discount_amount' => $pricing['discount_amount'],
            'final_price' => $pricing['final_price'],
            'applied_discount' => $pricing['applied_discount'],
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}

