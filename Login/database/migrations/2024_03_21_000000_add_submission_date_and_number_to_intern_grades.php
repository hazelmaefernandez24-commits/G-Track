<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('intern_grades', function (Blueprint $table) {
            $table->date('submission_date')->after('company_name');
            $table->enum('submission_number', ['1st', '2nd', '3rd', '4th'])->after('submission_date');
        });
    }

    public function down()
    {
        Schema::table('intern_grades', function (Blueprint $table) {
            $table->dropColumn(['submission_date', 'submission_number']);
        });
    }
}; 