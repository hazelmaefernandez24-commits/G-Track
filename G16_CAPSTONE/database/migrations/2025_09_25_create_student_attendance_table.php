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
        Schema::create('student_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->date('attendance_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('absent');
            $table->text('remarks')->nullable();
            $table->boolean('task_eligible')->default(false); // Can be assigned tasks
            $table->timestamps();
            
            // Foreign key constraint - removed for now to avoid issues
            // $table->foreign('student_id')->references('user_id')->on('login')->onDelete('cascade');
            
            // Unique constraint to prevent duplicate entries per day
            $table->unique(['student_id', 'attendance_date']);
            
            // Indexes for better performance
            $table->index(['attendance_date', 'status']);
            $table->index(['student_id', 'attendance_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_attendance');
    }
};
