<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the  migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 10)->unique();
            $table->string('name', 255);
            $table->integer('capacity')->default(6);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('description')->nullable();

            // General gender capacity controls
            $table->integer('male_capacity')->nullable()->comment('General male capacity');
            $table->integer('female_capacity')->nullable()->comment('General female capacity');

            // Batch-specific gender capacity controls
            $table->integer('male_capacity_2025')->nullable()->comment('Male capacity for batch 2025');
            $table->integer('female_capacity_2025')->nullable()->comment('Female capacity for batch 2025');
            $table->integer('male_capacity_2026')->nullable()->comment('Male capacity for batch 2026');
            $table->integer('female_capacity_2026')->nullable()->comment('Female capacity for batch 2026');

            // Assigned batch for the room (if room is dedicated to specific batch)
            $table->string('assigned_batch', 10)->nullable()->comment('Batch assigned to this room (2025/2026)');

            $table->timestamps();

            // Indexes for performance
            $table->index('room_number');
            $table->index('status');
            $table->index(['status', 'capacity']);
            $table->index('assigned_batch');
            $table->index(['assigned_batch', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
