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
        // Commented out (duplicate migration) – see 2025_04_28_153544_add_failing_grade_columns_to_schools_table.php
        // Schema::table('schools', function (Blueprint $table) {
        //     $table->decimal('failing_grade_min', 3, 1)->after('passing_grade_max');
        //     $table->decimal('failing_grade_max', 3, 1)->after('failing_grade_min');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Commented out (duplicate migration) – see 2025_04_28_153544_add_failing_grade_columns_to_schools_table.php
        // Schema::table('schools', function (Blueprint $table) {
        //     $table->dropColumn(['failing_grade_min', 'failing_grade_max']);
        // });
    }
};
