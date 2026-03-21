<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('going_outs', function (Blueprint $table) {
            $table->unique(['student_id', 'going_out_date', 'session_number'], 'going_outs_student_date_session_unique');
        });
    }

    public function down(): void
    {
        Schema::table('going_outs', function (Blueprint $table) {
            $table->dropUnique('going_outs_student_date_session_unique');
        });
    }
};