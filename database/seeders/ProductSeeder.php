<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $leafCategoryIds = Category::query()
            ->whereDoesntHave('children')
            ->pluck('id');

        if ($leafCategoryIds->isEmpty()) {
            $leafCategoryIds = Category::factory()->count(5)->create()->pluck('id');
        }

        Product::factory()
            ->count(60)
            ->make()
            ->each(function (Product $product) use ($leafCategoryIds) {
                $product->category_id = $leafCategoryIds->random();
                $product->save();
            });
    }
}

