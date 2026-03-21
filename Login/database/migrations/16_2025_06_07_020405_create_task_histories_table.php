<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('task_histories', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 50);
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('day', 20);
            $table->string('week', 20);
            $table->string('month', 20);
            $table->string('year', 10);
            $table->boolean('completed')->default(false);
            $table->string('status')->default('pending');
            $table->timestamps();

            // Add a unique constraint to prevent duplicate entries
            $table->unique(['room_number', 'day', 'week', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('task_histories');
    }
};
