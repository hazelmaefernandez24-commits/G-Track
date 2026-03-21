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
        Schema::create('generaltask_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('dynamic_tasks')->onDelete('cascade');
            $table->string('batch_year'); // e.g., "2025", "2026"
            $table->enum('gender', ['male', 'female']);
            $table->integer('assigned_count'); // Number of students assigned
            $table->date('assignment_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generaltask_assignments');
    }
};
