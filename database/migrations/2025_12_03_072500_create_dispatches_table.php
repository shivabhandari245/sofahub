<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('batch_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->integer('quantity');
            $table->string('driver')->nullable();
            $table->string('status')->default('Pending');

            $table->dateTime('received_date')->nullable();
            $table->dateTime('delivered_date')->nullable();
            $table->string('remarks')->nullable();

            $table->timestamps();

            // Foreign keys
            $table->foreign('batch_id')
                ->references('id')->on('batches')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
