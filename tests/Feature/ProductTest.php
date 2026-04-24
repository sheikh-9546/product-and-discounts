<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'super@module.com')->first();
    }

    public function test_can_list_products_with_pagination(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'name', 'price', 'final_price', 'discount_amount'],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_can_search_products(): void
    {
        $product = Product::factory()->create(['name' => 'Unique Test Product']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products?search=Unique Test');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertTrue(collect($data)->contains('id', $product->id));
    }

    public function test_can_filter_products_by_category(): void
    {
        $category = Category::first();
        $product  = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/products?category_id={$category->id}");

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertTrue(collect($data)->contains('id', $product->id));
    }

    public function test_can_filter_products_with_subcategories(): void
    {
        $parentCategory = Category::whereNull('parent_id')->first();
        $childCategory  = Category::where('parent_id', $parentCategory->id)->first();

        if ($childCategory) {
            $product = Product::factory()->create(['category_id' => $childCategory->id]);

            $response = $this->actingAs($this->user, 'sanctum')
                ->getJson("/api/v1/products?category_id={$parentCategory->id}&include_subcategories=true");

            $response->assertOk();
            $data = $response->json('data.data');
            $this->assertTrue(collect($data)->contains('id', $product->id));
        } else {
            $this->markTestSkipped('No child categories available');
        }
    }

    public function test_can_sort_products(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/products?sort_column=price&sort_direction=asc');

        $response->assertOk();
    }

    public function test_can_show_single_product_with_discounts(): void
    {
        $product = Product::first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/products/{$product->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'discount_amount',
                    'final_price',
                    'applied_discount',
                    'category',
                ],
            ]);
    }

    public function test_can_create_product(): void
    {
        $category = Category::first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/products', [
                'name'        => 'New Test Product',
                'description' => 'A test product description',
                'price'       => 99.99,
                'category_id' => $category->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'New Test Product');

        $this->assertDatabaseHas('products', ['name' => 'New Test Product']);
    }

    public function test_product_slug_is_auto_generated(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/products', [
                'name'  => 'Auto Slug Test Product',
                'price' => 50.00,
            ]);

        $response->assertStatus(201);

        $product = Product::where('name', 'Auto Slug Test Product')->first();
        $this->assertNotNull($product->slug);
        $this->assertStringContainsString('auto-slug-test-product', $product->slug);
    }

    public function test_can_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/products/{$product->id}", [
                'name'  => 'Updated Product Name',
                'price' => 149.99,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'Updated Product Name');
    }

    public function test_can_attach_discounts_to_product(): void
    {
        $product  = Product::factory()->create();
        $discount = Discount::first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/products/{$product->id}", [
                'name'         => $product->name,
                'price'        => $product->price,
                'discount_ids' => [$discount->id],
            ]);

        $response->assertOk();
        $this->assertTrue($product->fresh()->discounts->contains($discount));
    }

    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/products/{$product->id}");

        $response->assertOk();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    public function test_product_validation_requires_name_and_price(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'price']);
    }
}
