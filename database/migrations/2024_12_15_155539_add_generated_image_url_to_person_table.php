<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('person', function (Blueprint $table) {
            // Add the column for storing the image URL
            $table->string('generated_image_url')->nullable(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('person', function (Blueprint $table) {
            // Drop the column if we rollback the migration
            $table->dropColumn('generated_image_url');
        });
    }
};
