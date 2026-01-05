<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();

            // Foreign key columns + indexes
            $table->unsignedBigInteger('batchproduct_id')->index();


            $table->string('leader_name');
            $table->integer('quantity');
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('other_expenses', 10, 2)->nullable();

            $table->decimal('total_cost', 10, 2)->nullable();
            $table->decimal('expected_unit_cost', 10, 2)->nullable();
            $table->date('start_date');
            $table->date('expected_completion');
            $table->string("status")->default('Pending');

            // Foreign key relationships
            $table->foreign('batchproduct_id')
                ->references('id')->on('batch_products')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
