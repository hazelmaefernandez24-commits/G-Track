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
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->string('school_id');
            $table->string('class_id');
            $table->foreignId('grade_submission_id')->constrained('grade_submissions')->onDelete('cascade');
            $table->integer('student_count')->default(0);
            $table->enum('status', ['pending', 'done'])->default('pending');
            $table->date('intervention_date')->nullable();
            $table->string('educator_assigned')->nullable();
            $table->text('remarks')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('cascade');
            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
            $table->foreign('educator_assigned')->references('user_id')->on('pnph_users')->onDelete('set null');
            $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('set null');
            $table->foreign('updated_by')->references('user_id')->on('pnph_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
