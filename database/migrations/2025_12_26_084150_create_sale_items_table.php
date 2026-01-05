<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('product_id');
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 10, 2);
             
            $table->decimal('subtotal', 10, 2);
            $table->decimal('profit', 10, 2);
            $table->decimal('returned_quantity', 10, 2)->default(0);
            $table->boolean('is_returned')->default(false);
            $table->timestamp('returned_at')->nullable();
            $table->text('return_reason')->nullable();
            $table->string('status')->default("sold");
            $table->timestamps();

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
    }
};
