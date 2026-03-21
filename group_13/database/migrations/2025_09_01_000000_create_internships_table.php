<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->string('school_id');
            $table->unsignedBigInteger('class_id');
            $table->string('student_id'); // references pnph_users.user_id
            $table->string('company')->nullable();
            $table->string('time_of_duty');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->timestamps();
           
            $table->index(['school_id']);
            $table->index(['class_id']);
            $table->index(['student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};


