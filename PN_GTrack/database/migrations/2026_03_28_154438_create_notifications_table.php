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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->nullable(); // optional if tied to a student
            $table->enum('type', ['sos', 'broadcast']); // SOS or Broadcast
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->timestamps();

            // optional foreign key if you have a students table
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};