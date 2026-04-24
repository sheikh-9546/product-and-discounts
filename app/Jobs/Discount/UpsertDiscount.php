<?php

namespace App\Jobs\Discount;

use App\Http\Requests\Discount\UpsertDiscountRequest;
use App\Models\Discount;
use Illuminate\Foundation\Bus\Dispatchable;

class UpsertDiscount
{
    use Dispatchable;

    public function __construct(
        private readonly array $payload,
        private readonly array $productIds,
        private readonly ?Discount $discount = null,
    ) {}

    public static function forCreate(UpsertDiscountRequest $request): static
    {
        return new static($request->payload(), $request->getProductIds(), null);
    }

    public static function forUpdate(UpsertDiscountRequest $request, Discount $discount): static
    {
        return new static($request->payload(), $request->getProductIds(), $discount);
    }

    public function handle(): Discount
    {
        $discount = $this->discount
            ? tap($this->discount)->update($this->payload)
            : Discount::create($this->payload);

        if (is_array($this->productIds)) {
            $discount->products()->sync($this->productIds);
        }

        return $discount->fresh(['products']);
    }
}
