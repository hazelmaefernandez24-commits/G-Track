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
            $table->string('user_id'); // Change to string to match pnph_users.user_id
            $table->unsignedBigInteger('grade_submission_id');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        
            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->foreign('grade_submission_id')->references('id')->on('grade_submissions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};