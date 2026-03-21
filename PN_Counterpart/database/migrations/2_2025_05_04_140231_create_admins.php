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

        Schema::create('admins', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('user_id'); // Foreign key to pnph_users table
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->string('position', 50)->nullable();
            $table->timestamps(); // Adds created_at and updated_at columns

            // Foreign key constraint
            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
