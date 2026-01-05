<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dispatch_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('category');
            $table->string('quality');
            $table->decimal('quantity',10,2);
            $table->decimal('cost_per_product', 10, 2);
            $table->decimal('total_cost', 10, 2);
            $table->string('source');
            $table->timestamps();
            
             $table->foreign('dispatch_id')
                ->references('id')->on('dispatches')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
            
            
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
