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
            $table->string('name'); // e.g., "Kitchen & Dining", "General Cleaning", "Mood Preparation"
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0); // For ordering categories
            $table->boolean('is_active')->default(true);
            $table->json('requirements')->nullable(); // Store specific requirements like student count, gender split
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
