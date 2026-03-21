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
        Schema::create('assignments_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('student_group16_id');
            $table->foreign('student_group16_id')->references('id')->on('student_group16')->onDelete('cascade');
            $table->boolean('is_coordinator')->default(false);
            $table->text('comments')->nullable();
            $table->timestamp('comment_created_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments_members');
    }
};
