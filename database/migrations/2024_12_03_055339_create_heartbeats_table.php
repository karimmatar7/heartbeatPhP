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
        Schema::create('heartbeats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id'); 
            $table->integer('heart_rate');
            $table->integer('heart_beat');
            $table->float('humidity', 8, 2);
            $table->timestamps();

            // Define the foreign key
            $table->foreign('person_id')
                  ->references('id')
                  ->on('people')
                  ->onDelete('cascade'); // Optional: cascade delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};
