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
        Schema::create('dynamic_task_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('dynamic_task_assignments')->onDelete('cascade');
            $table->string('student_id'); // References pnph_users.user_id
            $table->foreign('student_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->boolean('is_coordinator')->default(false);
            $table->text('comments')->nullable();
            $table->timestamp('comment_created_at')->nullable();
            $table->string('assigned_by'); // Admin who assigned this student
            $table->foreign('assigned_by')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->timestamps();
            
            // Ensure unique student per assignment
            $table->unique(['assignment_id', 'student_id']);
            
            // Indexes for performance
            $table->index('student_id');
            $table->index(['assignment_id', 'is_coordinator']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_task_members');
    }
};
