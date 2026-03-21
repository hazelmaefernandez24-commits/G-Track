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
        Schema::create('severities', function (Blueprint $table) {
    $table->id();
    $table->string('severity_name')->unique(); // e.g., Low, Medium
    $table->integer('max_infraction');         // e.g., 4, 3, 2
    $table->boolean('is_active')->default(true); // Allows disabling without deleting
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('severities');
    }
};
