<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::create('batch_products', function (Blueprint $table) {
    $table->id();
    $table->string("name");
        
    $table->unsignedBigInteger('productquality_id')->nullable()->index();
    $table->unsignedBigInteger('productcategory_id')->nullable()->index(); 
    $table->decimal('material_cost', 12, 2)->nullable();
    $table->timestamps();

    $table->foreign('productquality_id')
                ->references('id')->on('product_quality')
                ->nullOnDelete();;

    $table->foreign('productcategory_id')
                ->references('id')->on('product_categories')
                ->nullOnDelete();;
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batch_products');
    }
};
