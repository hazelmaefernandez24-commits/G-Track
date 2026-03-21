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
        Schema::create('student_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('category_id');
            $table->string('student_name');
            $table->string('category_name');
            $table->string('day'); // monday, tuesday, etc.
            $table->date('date'); // specific date
            $table->string('task_type')->nullable();
            $table->string('time_slot')->nullable();
            $table->string('task_area')->default('General');
            // TEXT columns cannot have a default value in MySQL. Make nullable and
            // set any desired default at the application/seeder level instead.
            $table->text('task_description')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed'])->default('assigned');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['student_id', 'date']);
            $table->index(['category_id', 'day', 'date']);
            $table->index('status');
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['student_id', 'category_id', 'day', 'date'], 'unique_student_task_assignment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_task_assignments');
    }
};
