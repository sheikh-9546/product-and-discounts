<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
            $leafCategoryIds = Category::factory()
                ->count(5)
                ->create()
                ->pluck('id');
        }

        Product::factory()
            ->count(60)
            ->create()
            ->each(function (Product $product) use ($leafCategoryIds) {

                $product->update([
                    'category_id' => $leafCategoryIds->random(),
                    'slug' => Str::slug($product->name) . '-' . uniqid(),
                ]);
            });
    }
}