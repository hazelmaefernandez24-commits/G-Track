<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('grade_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->string('class_id');
            $table->string('semester');
            $table->string('term');
            $table->string('academic_year');
            $table->json('subject_ids'); // we store selected subjects as array
            $table->timestamps();

            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('cascade');
            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('grade_submissions');
    }
};
