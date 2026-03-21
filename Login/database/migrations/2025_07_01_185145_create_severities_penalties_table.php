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
        Schema::create('severity_penalties', function (Blueprint $table) {
        $table->id();
    $table->foreignId('severity_id')->constrained('severities')->onDelete('cascade');
    $table->integer('infraction_number'); // e.g., 1, 2, 3
    $table->string('penalty');            // e.g., Verbal Warning
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('severities_penalties');
    }
};
