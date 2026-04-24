<?php

namespace App\Jobs\Product;

use App\Http\Requests\Product\UpsertProductRequest;
use App\Models\Product;
use Illuminate\Foundation\Bus\Dispatchable;

class UpsertProduct
{
    use Dispatchable;

    public function __construct(
        private readonly array $payload,
        private readonly array $discountIds,
        private readonly ?Product $product = null,
    ) {}

    public static function forCreate(UpsertProductRequest $request): static
    {
        return new static($request->payload(), $request->getDiscountIds(), null);
    }

    public static function forUpdate(UpsertProductRequest $request, Product $product): static
    {
        return new static($request->payload(), $request->getDiscountIds(), $product);
    }

    public function handle(): Product
    {
        $product = $this->product
            ? tap($this->product)->update($this->payload)
            : Product::create($this->payload);

        if (is_array($this->discountIds)) {
            $product->discounts()->sync($this->discountIds);
        }

        return $product->fresh(['category', 'discounts']);
    }
}
