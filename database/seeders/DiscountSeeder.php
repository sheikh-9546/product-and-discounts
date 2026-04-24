<?php

namespace Database\Seeders;

use App\Models\Discount;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DiscountSeeder extends Seeder
{
    public function run(): void
    {
        if (Discount::query()->exists()) {
            return;
        }

        /** @var Collection<int, Discount> $discounts */
        $discounts = Discount::factory()->count(10)->create();

        $products = Product::query()->inRandomOrder()->limit(20)->get();
        foreach ($products as $product) {
            $product->discounts()->syncWithoutDetaching($discounts->random(rand(1, 2))->pluck('id'));
        }
    }
}

