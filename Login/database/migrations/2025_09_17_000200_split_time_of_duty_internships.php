<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            if (!Schema::hasColumn('internships', 'time_in')) {
                $table->time('time_in')->nullable()->after('company');
            }
            if (!Schema::hasColumn('internships', 'time_out')) {
                $table->time('time_out')->nullable()->after('time_in');
            }
        });
    }

    public function down(): void
    {
        Schema::table('internships', function (Blueprint $table) {
            if (Schema::hasColumn('internships', 'time_out')) {
                $table->dropColumn('time_out');
            }
            if (Schema::hasColumn('internships', 'time_in')) {
                $table->dropColumn('time_in');
            }
        });
    }
};


