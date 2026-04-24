<?php

namespace App\Http\Resources\Product;

use App\Services\Pricing\DiscountCalculator;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductPaginateResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var DiscountCalculator $calculator */
        $calculator = app(DiscountCalculator::class);
        $pricing = $calculator->priceWithBestDiscount($this->resource);

        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name' => $this->name,
            'price' => $pricing['original_price'],
            'discount_amount' => $pricing['discount_amount'],
            'final_price' => $pricing['final_price'],
        ];
    }
}

