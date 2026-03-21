<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manual_entry_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('student_id');
            $table->string('log_type'); // 'academic' or 'going_out'
            $table->unsignedBigInteger('log_id')->nullable(); // related log id if any
            $table->string('entry_type'); // 'time_out', 'time_in', 'both'
            $table->text('reason');
            $table->string('monitor_name');
            $table->json('original_data')->nullable();
            $table->json('manual_data')->nullable();
            $table->string('status')->default('pending'); // 'pending','approved','rejected'
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->index(['student_id']);
            $table->index(['log_type']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_entry_logs');
    }
};