<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 12, 2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Optional: for MySQL full-text search (query has LIKE fallback).
        if (DB::connection()->getDriverName() === 'mysql') {
            Schema::table('products', function (Blueprint $table) {
                $table->fullText(['name', 'description']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

