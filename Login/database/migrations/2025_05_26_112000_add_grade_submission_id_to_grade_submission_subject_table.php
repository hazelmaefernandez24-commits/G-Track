<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('grade_submission_subject', function (Blueprint $table) {
            $table->foreignId('grade_submission_id')->constrained('grade_submissions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('grade_submission_subject', function (Blueprint $table) {
            $table->dropForeign(['grade_submission_id']);
            $table->dropColumn('grade_submission_id');
        });
    }
};
