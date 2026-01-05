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
        Schema::create('material_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')
                  ->constrained('rawmaterials')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
            
            $table->integer('old_quantity')->default(0);
            $table->integer('quantity_change');
            $table->integer('new_quantity');
            
            $table->decimal('old_unit_cost', 10, 2)->default(0);
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('total_cost_change', 12, 2)->default(0);
            
            $table->enum('type', ['initial_stock', 'restocked', 'used', 'adjusted', 'transferred'])
                  ->default('initial_stock');
            
            $table->text('notes')->nullable();
            
            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['raw_material_id', 'created_at']);
            $table->index('type');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_histories');
    }
};