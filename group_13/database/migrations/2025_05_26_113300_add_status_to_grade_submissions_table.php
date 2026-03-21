<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grade_submissions', function (Blueprint $table) {
            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending')->after('academic_year');
        });
    }

    public function down()
    {
        Schema::table('grade_submissions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
