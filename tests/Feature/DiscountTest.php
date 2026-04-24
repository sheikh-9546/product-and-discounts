<?php

namespace Tests\Feature;

use App\Models\Discount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'super@module.com')->first();
    }

    public function test_can_list_discounts_with_pagination(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/discounts?per_page=10');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'title', 'type', 'value'],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    public function test_can_search_discounts(): void
    {
        $discount = Discount::factory()->create(['title' => 'Unique Discount Title']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/discounts?search=Unique Discount');

        $response->assertOk();
        $data = $response->json('data.data');
        $this->assertTrue(collect($data)->contains('id', $discount->id));
    }

    public function test_can_sort_discounts(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/discounts?sort_column=value&sort_direction=desc');

        $response->assertOk();
    }

    public function test_can_show_single_discount(): void
    {
        $discount = Discount::first();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/discounts/{$discount->id}");

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'title', 'type', 'value'],
            ]);
    }

    public function test_can_create_percentage_discount(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/discounts', [
                'title' => 'Test Percentage Discount',
                'type'  => 'percentage',
                'value' => 15.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Percentage Discount')
            ->assertJsonPath('data.type', 'percentage');

        $this->assertDatabaseHas('discounts', ['title' => 'Test Percentage Discount']);
    }

    public function test_can_create_fixed_discount(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/discounts', [
                'title' => 'Test Fixed Discount',
                'type'  => 'fixed',
                'value' => 25.00,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.title', 'Test Fixed Discount')
            ->assertJsonPath('data.type', 'fixed');
    }

    public function test_can_update_discount(): void
    {
        $discount = Discount::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/discounts/{$discount->id}", [
                'title' => 'Updated Discount Title',
                'type'  => 'percentage',
                'value' => 20.00,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.title', 'Updated Discount Title');
    }

    public function test_can_delete_discount(): void
    {
        $discount = Discount::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/discounts/{$discount->id}");

        $response->assertOk();
        $this->assertSoftDeleted('discounts', ['id' => $discount->id]);
    }

    public function test_discount_type_must_be_valid(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/discounts', [
                'title' => 'Invalid Type Discount',
                'type'  => 'invalid',
                'value' => 10.00,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_discount_validation_requires_all_fields(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/discounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'type', 'value']);
    }
}
