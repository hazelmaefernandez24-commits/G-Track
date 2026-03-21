<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'failing_grade_min')) {
                $table->decimal('failing_grade_min', 3, 1)->after('passing_grade_max')->nullable();
            }
            if (!Schema::hasColumn('schools', 'failing_grade_max')) {
                $table->decimal('failing_grade_max', 3, 1)->after('failing_grade_min')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'failing_grade_max')) {
                $table->dropColumn('failing_grade_max');
            }
            if (Schema::hasColumn('schools', 'failing_grade_min')) {
                $table->dropColumn('failing_grade_min');
            }
        });
    }
};


