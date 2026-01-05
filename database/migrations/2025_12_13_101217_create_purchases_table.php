<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id');

            $table->string('product_name');
            $table->string('category');
            $table->string('supplier_name');
            $table->string('supplier_contact')->nullable();

            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->decimal('totalcost', 10, 2);
            $table->string('quality')->nullable();
            $table->date('delivery_date')->nullable();

            $table->string('status')->default('Purchased'); 

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('delivery_date');

            // Foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
