<?php

namespace App\Services\Pricing;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Support\Collection;

class DiscountCalculator
{
    /**
     * @return array{
     *   original_price: string,
     *   discount_amount: string,
     *   final_price: string,
     *   applied_discount: array|null
     * }
     */
    public function priceWithBestDiscount(Product $product): array
    {
        $original  = (float) $product->price;
        $discounts = $this->applicableDiscounts($product);

        $best       = null;
        $bestAmount = 0.0;

        foreach ($discounts as $candidate) {
            $amount = $this->discountAmount($candidate, $original);
            if ($amount > $bestAmount) {
                $bestAmount = $amount;
                $best       = $candidate;
            }
        }

        $bestAmount = min($bestAmount, $original);
        $final      = max(0.0, $original - $bestAmount);

        return [
            'original_price'   => number_format($original, 2, '.', ''),
            'discount_amount'  => number_format($bestAmount, 2, '.', ''),
            'final_price'      => number_format($final, 2, '.', ''),
            'applied_discount' => $best ? [
                'id'    => $best->id,
                'title' => $best->title,
                'type'  => $best->type,
                'value' => (string) $best->value,
            ] : null,
        ];
    }

    /**
     * @return Collection<int, Discount>
     */
    private function applicableDiscounts(Product $product): Collection
    {
        $productDiscounts = $product->relationLoaded('discounts')
            ? $product->discounts
            : $product->discounts()->get();

        return $productDiscounts->unique('id')->values();
    }

    private function discountAmount(Discount $discount, float $price): float
    {
        if ($price <= 0) {
            return 0.0;
        }

        if ($discount->type === 'percentage') {
            $pct = (float) $discount->value;

            return max(0.0, $price * ($pct / 100.0));
        }

        return max(0.0, (float) $discount->value);
    }
}
