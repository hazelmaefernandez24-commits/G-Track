<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('intern_grades', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->string('class_id');
            $table->string('intern_id');
            $table->string('company_name');
            $table->json('grades');
            $table->decimal('final_grade', 3, 1);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('school_id')->references('school_id')->on('schools')->onDelete('cascade');
            $table->foreign('class_id')->references('class_id')->on('classes')->onDelete('cascade');
            $table->foreign('intern_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('intern_grades');
    }
}; 