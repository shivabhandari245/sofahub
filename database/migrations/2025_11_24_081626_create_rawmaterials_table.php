<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rawmaterials', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            // Indexes for faster searching/filtering
            $table->index('category_id');
            $table->index('supplier_id');
            $table->index('unit_id');
            // Material info
            $table->string('name')->index(); // Search often by name
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->integer('quantity')->default(0)->index(); // Filter low-stock quickly
            $table->decimal('total_cost', 14, 2)->nullable();
            $table->string('status')->default('available')->index(); // Faster filtering
            $table->string('storage_location')->nullable();
            $table->timestamps();
            $table->foreign('category_id') ->references('id')->on('rawmaterialcategories')->cascadeOnUpdate() ->restrictOnDelete();
            $table->foreign('supplier_id') ->references('id')->on('suppliers')->cascadeOnUpdate()
                ->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rawmaterials');
    }
};
