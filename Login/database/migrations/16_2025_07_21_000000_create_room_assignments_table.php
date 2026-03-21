<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 10);
            $table->string('student_id', 50);
            $table->string('student_name', 255); 
            $table->enum('student_gender', ['M', 'F']); // Gender for room assignment validation
            $table->string('batch_year', 10)->nullable(); 
            $table->integer('assignment_order')->default(0); 
            $table->integer('room_capacity')->default(6); 
            $table->timestamp('assigned_at');
            $table->timestamps();

            $table->index('room_number');
            $table->index('student_id');
            $table->index(['room_number', 'assignment_order']);
            $table->index(['room_number', 'student_gender']); 
            $table->index('batch_year');
            
            // Ensure unique student per room
            $table->unique(['room_number', 'student_id']);
            
            // Foreign key constraints
            $table->foreign('student_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->foreign('room_number')->references('room_number')->on('rooms')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('room_assignments');
    }
};
