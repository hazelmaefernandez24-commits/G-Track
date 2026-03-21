<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->decimal('failing_grade_min', 3, 1)->after('passing_grade_max'); // Add minimum failing grade
            $table->decimal('failing_grade_max', 3, 1)->after('failing_grade_min'); // Add maximum failing grade
        });
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['failing_grade_min', 'failing_grade_max']); // Remove the columns
        });
    }
};