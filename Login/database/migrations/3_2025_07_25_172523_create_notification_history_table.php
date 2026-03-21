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
        Schema::create('notification_history', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('batch')->nullable();
            $table->enum('action_type', ['time_in', 'time_out']);
            $table->enum('log_type', ['academic', 'going_out', 'visitor', 'intern', 'going_home']);
            $table->boolean('is_late')->default(false);
            $table->boolean('is_read')->default(false);
            $table->timestamp('activity_timestamp');
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['student_id', 'created_at']);
            $table->index(['is_read', 'created_at']);
            $table->index(['log_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_history');
    }
};
