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
        $table->id(); // Internal DB ID
        $table->string('student_id')->unique(); // <--- ADD THIS LINE
        $table->string('name');
        $table->string('email')->unique();
        $table->enum('gender', ['Male', 'Female'])->default('Male');
        $table->enum('class', ['All Classes', '2026', '2027', '2028'])->default('2026');
        $table->boolean('status')->default(false);
        $table->integer('battery_level')->default(100);
        $table->string('signal_status')->nullable();
        $table->string('last_update')->nullable();
        $table->string('contact');
        $table->string('sos_status')->default('safe'); // Added for the map alerts
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
