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
        Schema::create('dynamic_task_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('color_code', 7)->default('#007bff'); // Hex color for UI
            $table->integer('max_students')->nullable(); // Maximum students allowed
            $table->integer('max_boys')->nullable(); // Maximum boys allowed
            $table->integer('max_girls')->nullable(); // Maximum girls allowed
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_task_categories');
    }
};
