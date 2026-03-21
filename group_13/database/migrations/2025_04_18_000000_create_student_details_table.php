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
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('student_id')->unique();
            $table->string('batch');
            $table->string('group', 2);
            $table->string('student_number', 4);
            $table->string('training_code', 2);
            $table->timestamps();

            // Foreign key relationship with pnph_users table
            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_details');
    }
}; 