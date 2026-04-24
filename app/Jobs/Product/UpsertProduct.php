<?php

namespace App\Jobs\Product;

use App\Http\Requests\Product\UpsertProductRequest;
use App\Models\Product;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Str;

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
        $data = $this->payload;

        if (! $this->product && empty($data['slug'])) {
            $data['slug'] = $this->generateUniqueSlug($data['name']);
        }

        $product = $this->product
            ? tap($this->product)->update($data)
            : Product::create($data);

        if (is_array($this->discountIds)) {
            $product->discounts()->sync($this->discountIds);
        }

        return $product->fresh(['category', 'discounts']);
    }

    private function generateUniqueSlug(string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug     = $baseSlug;
        $counter  = 1;

        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
