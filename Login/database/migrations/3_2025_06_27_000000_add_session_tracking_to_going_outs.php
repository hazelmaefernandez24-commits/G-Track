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
        Schema::table('going_outs', function (Blueprint $table) {
            // Add session number to track multiple sessions per day
            $table->integer('session_number')->default(1)->after('going_out_date');

            // Add session status to track if session is complete (both log out and log in done)
            $table->enum('session_status', ['active', 'completed'])->default('active')->after('session_number');

            // Add index for better performance when querying multiple sessions
            $table->index(['student_id', 'going_out_date', 'session_number'], 'going_outs_student_date_session_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('going_outs', function (Blueprint $table) {
            $table->dropIndex('going_outs_student_date_session_index');
            $table->dropColumn(['session_number', 'session_status']);
        });
    }
};
