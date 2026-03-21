<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('x_status', function (Blueprint $table) {
            if (!Schema::hasColumn('x_status', 'user_id')) {
                $table->string('user_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('x_status', 'student_id')) {
                $table->string('student_id')->nullable()->after('user_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('x_status', function (Blueprint $table) {
            if (Schema::hasColumn('x_status', 'student_id')) {
                $table->dropColumn('student_id');
            }
            if (Schema::hasColumn('x_status', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
