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
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('batsman');
            $table->boolean('isout')->default(false);
            $table->string('hisruns');
            $table->string('bowler');
            $table->string('onthisbowl');
            $table->integer('count');
            $table->integer('current_runs');
            $table->integer('current_wickets');
            $table->integer('current_over')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
