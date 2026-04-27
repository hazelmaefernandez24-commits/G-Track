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
    Schema::create('students', function (Blueprint $table) {
        $table->id();
        $table->string('student_id')->unique();
        $table->string('name');
        $table->string('class');
        $table->enum('gender', ['Male', 'Female']);
        $table->boolean('status')->default(false); // online/offline
        $table->timestamp('last_update')->nullable();
        $table->string('contact')->nullable();

        // 🔹 New fields for monitoring
        $table->integer('battery_level')->default(100); // percentage
        $table->string('signal_status')->default('Good'); // Good, Weak, None
        $table->string('location')->nullable(); // optional: latest location

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
