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
        Schema::create('dynamic_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('dynamic_task_categories')->onDelete('cascade');
            $table->string('name'); // e.g., "Clean the rugs", "Prepare dining area"
            $table->text('description'); // Clear and detailed description
            $table->json('subtasks'); // Array of specific subtasks
            $table->integer('estimated_duration_minutes')->nullable(); // How long it should take
            $table->integer('required_students')->default(1); // How many students needed
            $table->enum('gender_preference', ['any', 'male', 'female', 'mixed'])->default('any');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_tasks');
    }
};
