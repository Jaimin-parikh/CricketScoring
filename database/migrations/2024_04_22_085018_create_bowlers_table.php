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
        Schema::create('bowlers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');            
            $table->integer('runs')->default(0);            
            $table->integer('over')->default(0);    
            $table->integer('wickets')->default(0);    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bowlers');
    }
};
