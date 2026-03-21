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
            $table->string('user_mInitial');
            $table->string('user_suffix');
            $table->string('user_email')->unique();
            $table->string('user_role');
            $table->string('user_password');
            $table->enum('status', ['active', 'inactive'])->default('active'); // Status column
            $table->boolean('is_temp_password')->default(true); // Temporary password column
            $table->timestamps();
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
