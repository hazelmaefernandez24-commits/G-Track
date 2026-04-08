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
        Schema::create('student_auths', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->unique(); // Links to the student
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();

            // Optionally, add a foreign key constraint
            // $table->foreign('student_id')->references('student_id')->on('students')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_auths');
    }
};
