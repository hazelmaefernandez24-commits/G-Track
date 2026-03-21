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
        Schema::create('pnph_users', function (Blueprint $table) {
            $table->string('user_id')->primary();
            $table->string('user_fname');
            $table->string('user_lname');
            $table->string('user_mInitial')->nullable();
            $table->string('user_suffix')->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->string('user_email')->unique();
            $table->string('user_role');
            $table->string('user_password');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->boolean('is_temp_password')->default(true);
            $table->string('token')->nullable();
            $table->timestamps();
        });

        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('student_id')->unique();
            $table->string('batch', 20);
            $table->string('group', 3);
            $table->string('student_number', 10);
            $table->string('training_code', 2);
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pnph_users');
    }
};
