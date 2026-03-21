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
        Schema::create('violation_appeals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained('violations')->onDelete('cascade');
            $table->string('student_id'); // Reference to student who appealed
            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->text('student_reason')->comment('Student\'s reason for appealing the violation');
            $table->enum('status', ['pending', 'approved', 'denied'])->default('pending');
            $table->text('admin_response')->nullable()->comment('Administrator\'s response to the appeal');
            $table->timestamp('appeal_date')->useCurrent()->comment('When the appeal was submitted');
            $table->timestamp('admin_decision_date')->nullable()->comment('When the admin made a decision');
            $table->string('reviewed_by')->nullable()->comment('Admin user ID who reviewed the appeal');
            $table->foreign('reviewed_by')->references('user_id')->on('pnph_users')->onDelete('set null');
            $table->text('additional_evidence')->nullable()->comment('Any additional evidence provided by student');
            $table->timestamps();

            // Add indexes for performance
            $table->index(['violation_id', 'status']);
            $table->index(['student_id', 'status']);
            $table->index('appeal_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_appeals');
    }
};
