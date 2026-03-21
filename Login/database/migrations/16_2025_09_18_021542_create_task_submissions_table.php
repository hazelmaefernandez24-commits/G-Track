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
        Schema::create('task_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id'); // Student who submitted
            $table->string('task_category'); // Kitchen, Dining, etc.
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->enum('status', ['pending', 'valid', 'invalid'])->default('pending');
            $table->string('validated_by')->nullable(); // Admin who validated
            $table->timestamp('validated_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            // Foreign key to users table
            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_submissions');
    }
};
