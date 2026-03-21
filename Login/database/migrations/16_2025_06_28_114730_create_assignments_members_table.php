<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assignments_members')) {
            Schema::create('assignments_members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->onDelete('cascade');
                // `pnph_users.user_id` is a string primary key, so student_id must be a string too
                $table->string('student_id');
                // Reference the Login users table primary key (user_id) so student_id matches the seeder
                $table->foreign('student_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
                $table->boolean('is_coordinator')->default(false);
                $table->text('comments')->nullable();
                $table->timestamp('comment_created_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments_members');
    }
};
