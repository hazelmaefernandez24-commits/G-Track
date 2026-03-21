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
        Schema::create('invalid_students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('g16_submission_id'); // Link to G16_CAPSTONE submission
            $table->string('g16_user_id'); // G16_CAPSTONE user ID
            $table->string('student_name'); // Full student name
            $table->string('student_id_code')->nullable(); // Student ID like 2025010027C1
            $table->string('student_email')->nullable();
            $table->string('gender')->nullable();
            $table->string('batch')->nullable();
            $table->string('task_category'); // Kitchen, Dining, etc.
            $table->text('description')->nullable(); // Task description
            $table->string('validated_by')->nullable(); // Who marked it invalid
            $table->timestamp('validated_at')->nullable(); // When marked invalid
            $table->text('admin_notes')->nullable();
            $table->timestamp('caught_at'); // When we caught this record
            $table->enum('status', ['caught', 'processed'])->default('caught');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('g16_submission_id');
            $table->index('g16_user_id');
            $table->index('student_id_code');
            $table->index('status');
            $table->index('caught_at');
            $table->index('validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invalid_students');
    }
};
