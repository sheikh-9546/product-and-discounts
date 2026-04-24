<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_discount', function (Blueprint $table) {
            $table->id()->startingValue(4001);
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('discount_id')->constrained('discounts')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->bigInteger('created_by')->nullable()->index();
            $table->bigInteger('updated_by')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_discount');
    }
};
