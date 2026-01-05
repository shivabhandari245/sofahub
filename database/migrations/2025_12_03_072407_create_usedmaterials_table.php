<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usedmaterials', function (Blueprint $table) {
            $table->id();

            // Foreign keys + indexes
            $table->unsignedBigInteger('batchproduct_id')->index();
            $table->unsignedBigInteger('raw_material_id')->index();

            $table->decimal('quantity_used', 10, 2)->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost', 12, 2)->nullable();
            $table->string('status')->default(0);
            $table->timestamps();

            // Relationships
            $table->foreign('batchproduct_id')
                ->references('id')->on('batch_products')
                ->onDelete('cascade');

            $table->foreign('raw_material_id')
                ->references('id')->on('rawmaterials')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usedmaterials');
    }
};
