<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    
    public function up(): void
    {
      
    Schema::create('customers', function (Blueprint $table) {
    $table->bigIncrements('id');
    $table->string('name');
    $table->string('email')->nullable()->unique();
    $table->string('phone', 20)->unique();
    $table->string('address')->nullable();
        $table->timestamps();
           $table->softDeletes();
});

    }

   
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};