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
        Schema::create('generated_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->string('category_name')->nullable();
            $table->date('schedule_date');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('student_name')->nullable();
            $table->string('task_title')->nullable();
            $table->text('task_description')->nullable();
            $table->string('batch')->nullable(); // 2025, 2026, etc.
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('rotation_frequency')->default('Daily'); // Daily, Weekly, etc.
            $table->json('schedule_data')->nullable(); // Store full schedule data as JSON
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            
            // Indexes
            $table->index('assignment_id');
            $table->index('student_id');
            $table->index('schedule_date');
            $table->index('batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('generated_schedules');
    }
};
