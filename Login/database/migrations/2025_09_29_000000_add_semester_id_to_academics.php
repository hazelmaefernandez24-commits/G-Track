<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('academics') && !Schema::hasColumn('academics', 'semester_id')) {
            Schema::table('academics', function (Blueprint $table) {
                $table->string('semester_id')->nullable()->after('monitor_logged_out');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('academics') && Schema::hasColumn('academics', 'semester_id')) {
            Schema::table('academics', function (Blueprint $table) {
                $table->dropColumn('semester_id');
            });
        }
    }
};
