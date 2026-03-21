<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('academics', function (Blueprint $table) {
            $table->unique(['student_id', 'academic_date'], 'academics_student_date_unique');
        });
    }

    public function down(): void
    {
        Schema::table('academics', function (Blueprint $table) {
            $table->dropUnique('academics_student_date_unique');
        });
    }
};