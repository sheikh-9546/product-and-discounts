<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        if (Category::query()->exists()) {
            return;
        }

        $electronics = Category::create(['name' => 'Electronics']);
        $fashion = Category::create(['name' => 'Fashion']);
        $home = Category::create(['name' => 'Home']);

        $phones = Category::create(['parent_id' => $electronics->id, 'name' => 'Phones']);
        Category::create(['parent_id' => $phones->id, 'name' => 'Smartphones']);
        Category::create(['parent_id' => $phones->id, 'name' => 'Accessories']);

        $mens = Category::create(['parent_id' => $fashion->id, 'name' => "Men's"]);
        $womens = Category::create(['parent_id' => $fashion->id, 'name' => "Women's"]);
        Category::create(['parent_id' => $mens->id, 'name' => 'Shoes']);
        Category::create(['parent_id' => $womens->id, 'name' => 'Bags']);

        $kitchen = Category::create(['parent_id' => $home->id, 'name' => 'Kitchen']);
        Category::create(['parent_id' => $kitchen->id, 'name' => 'Appliances']);
        Category::create(['parent_id' => $home->id, 'name' => 'Furniture']);
    }
}

