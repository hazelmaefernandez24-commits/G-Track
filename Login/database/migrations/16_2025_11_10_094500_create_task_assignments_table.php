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
        // Create task_definitions table - stores available tasks for each area
        if (! Schema::hasTable('task_definitions')) {
            Schema::create('task_definitions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id'); // Links to categories table
                $table->string('task_name');
                $table->text('task_description')->nullable();
                $table->integer('estimated_duration')->nullable(); // in minutes
                $table->enum('difficulty_level', ['easy', 'medium', 'hard'])->default('medium');
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['category_id', 'is_active']);
            });
        }

        // Create task_assignments table - stores actual task assignments to students
        if (! Schema::hasTable('task_assignments')) {
            Schema::create('task_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id'); // Links to assignments table
            $table->string('student_id'); // Links to pnph_users.user_id
            $table->unsignedBigInteger('task_definition_id'); // Links to task_definitions
            $table->date('assigned_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->enum('status', ['assigned', 'in_progress', 'completed', 'not_completed'])->default('assigned');
            $table->text('notes')->nullable();
            $table->string('assigned_by')->nullable(); // Who assigned this task
            $table->timestamps();
            
            $table->index(['assignment_id', 'assigned_date']);
            $table->index(['student_id', 'assigned_date']);
            $table->unique(['assignment_id', 'student_id', 'task_definition_id', 'assigned_date'], 'unique_task_assignment');
            });
        }

        // Create task_schedules table - stores weekly/daily task schedules
        if (! Schema::hasTable('task_schedules')) {
            Schema::create('task_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id'); // Links to assignments table
            $table->date('schedule_date');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->json('task_assignments'); // Stores the complete task assignment data for the day
            $table->enum('status', ['draft', 'active', 'completed'])->default('draft');
            $table->string('created_by')->nullable(); // Admin who created the schedule
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();
            
            $table->index(['assignment_id', 'schedule_date']);
            $table->index(['schedule_date', 'status']);
            });
        }

        // Create task_completion_logs table - tracks task completion
        if (! Schema::hasTable('task_completion_logs')) {
            Schema::create('task_completion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('task_assignment_id'); // Links to task_assignments
            $table->string('student_id'); // Links to pnph_users.user_id
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('completion_status', ['completed', 'partially_completed', 'not_completed', 'skipped'])->default('not_completed');
            $table->text('completion_notes')->nullable();
            $table->json('completion_evidence')->nullable(); // Photos, signatures, etc.
            $table->integer('quality_rating')->nullable(); // 1-5 rating
            $table->string('verified_by')->nullable(); // Coordinator who verified
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            
            $table->index(['task_assignment_id', 'completion_status']);
            $table->index(['student_id', 'completed_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('task_completion_logs');
    Schema::dropIfExists('task_schedules');
    Schema::dropIfExists('task_assignments');
    Schema::dropIfExists('task_definitions');
    }
};
