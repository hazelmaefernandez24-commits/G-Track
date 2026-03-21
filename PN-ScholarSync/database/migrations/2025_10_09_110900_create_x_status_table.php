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
        Schema::create('g16_task_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('room_number', 50);
            $table->unsignedBigInteger('task_id')->nullable();
            $table->string('day', 20);
            $table->string('week', 20);
            $table->string('month', 20);
            $table->string('year', 10);

            // Status/flags
            $table->boolean('completed')->default(false);
            $table->string('status')->default('pending');

            // Columns added later in G16 (make them part of the base table here)
            $table->string('assigned_to')->nullable();
            $table->string('task_area')->nullable();
            $table->text('task_description')->nullable();
            $table->string('filter_type')->default('daily');

            $table->timestamps();

            // Mirror the latest G16 unique constraint that includes task_id
            $table->unique([
                'room_number', 'task_id', 'day', 'week', 'month', 'year'
            ], 'g16_task_histories_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('g16_task_histories');
    }
};
