<?php

namespace Tests\Unit;

use App\Models\Discount;
use App\Models\Product;
use App\Services\Pricing\DiscountCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscountCalculatorTest extends TestCase
{
    use RefreshDatabase;

    private DiscountCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DiscountCalculator;
    }

    public function test_calculates_percentage_discount_correctly(): void
    {
        $product  = Product::factory()->create(['price' => 100.00]);
        $discount = Discount::factory()->create(['type' => 'percentage', 'value' => 20]);
        $product->discounts()->attach($discount);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('100.00', $result['original_price']);
        $this->assertEquals('20.00', $result['discount_amount']);
        $this->assertEquals('80.00', $result['final_price']);
        $this->assertEquals($discount->id, $result['applied_discount']['id']);
    }

    public function test_calculates_fixed_discount_correctly(): void
    {
        $product  = Product::factory()->create(['price' => 100.00]);
        $discount = Discount::factory()->create(['type' => 'fixed', 'value' => 25]);
        $product->discounts()->attach($discount);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('100.00', $result['original_price']);
        $this->assertEquals('25.00', $result['discount_amount']);
        $this->assertEquals('75.00', $result['final_price']);
    }

    public function test_selects_best_discount_when_multiple_exist(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);

        $smallDiscount = Discount::factory()->create(['type' => 'percentage', 'value' => 10]);
        $largeDiscount = Discount::factory()->create(['type' => 'percentage', 'value' => 30]);

        $product->discounts()->attach([$smallDiscount->id, $largeDiscount->id]);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('30.00', $result['discount_amount']);
        $this->assertEquals('70.00', $result['final_price']);
        $this->assertEquals($largeDiscount->id, $result['applied_discount']['id']);
    }

    public function test_fixed_discount_chosen_over_percentage_when_better(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);

        $percentageDiscount = Discount::factory()->create(['type' => 'percentage', 'value' => 10]);
        $fixedDiscount      = Discount::factory()->create(['type' => 'fixed', 'value' => 50]);

        $product->discounts()->attach([$percentageDiscount->id, $fixedDiscount->id]);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('50.00', $result['discount_amount']);
        $this->assertEquals($fixedDiscount->id, $result['applied_discount']['id']);
    }

    public function test_returns_zero_discount_when_no_discounts(): void
    {
        $product = Product::factory()->create(['price' => 100.00]);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('100.00', $result['original_price']);
        $this->assertEquals('0.00', $result['discount_amount']);
        $this->assertEquals('100.00', $result['final_price']);
        $this->assertNull($result['applied_discount']);
    }

    public function test_discount_cannot_exceed_product_price(): void
    {
        $product  = Product::factory()->create(['price' => 50.00]);
        $discount = Discount::factory()->create(['type' => 'fixed', 'value' => 100]);
        $product->discounts()->attach($discount);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('50.00', $result['discount_amount']);
        $this->assertEquals('0.00', $result['final_price']);
    }

    public function test_handles_zero_price_product(): void
    {
        $product  = Product::factory()->create(['price' => 0.00]);
        $discount = Discount::factory()->create(['type' => 'percentage', 'value' => 20]);
        $product->discounts()->attach($discount);

        $result = $this->calculator->priceWithBestDiscount($product);

        $this->assertEquals('0.00', $result['original_price']);
        $this->assertEquals('0.00', $result['discount_amount']);
        $this->assertEquals('0.00', $result['final_price']);
    }
}
