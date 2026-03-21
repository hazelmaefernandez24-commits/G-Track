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
        Schema::table('rooms', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('rooms', 'male_capacity')) {
                $table->integer('male_capacity')->nullable()->comment('General male capacity');
            }
            if (!Schema::hasColumn('rooms', 'female_capacity')) {
                $table->integer('female_capacity')->nullable()->comment('General female capacity');
            }
            if (!Schema::hasColumn('rooms', 'male_capacity_2025')) {
                $table->integer('male_capacity_2025')->nullable()->comment('Male capacity for batch 2025');
            }
            if (!Schema::hasColumn('rooms', 'female_capacity_2025')) {
                $table->integer('female_capacity_2025')->nullable()->comment('Female capacity for batch 2025');
            }
            if (!Schema::hasColumn('rooms', 'male_capacity_2026')) {
                $table->integer('male_capacity_2026')->nullable()->comment('Male capacity for batch 2026');
            }
            if (!Schema::hasColumn('rooms', 'female_capacity_2026')) {
                $table->integer('female_capacity_2026')->nullable()->comment('Female capacity for batch 2026');
            }
            if (!Schema::hasColumn('rooms', 'assigned_batch')) {
                $table->string('assigned_batch', 10)->nullable()->comment('Batch assigned to this room (2025/2026)');
            }
        });

        // Add indexes if they don't exist
        try {
            Schema::table('rooms', function (Blueprint $table) {
                $table->index('assigned_batch');
                $table->index(['assigned_batch', 'status']);
            });
        } catch (Exception $e) {
            // Indexes might already exist, ignore the error
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn([
                'male_capacity',
                'female_capacity',
                'male_capacity_2025',
                'female_capacity_2025',
                'male_capacity_2026',
                'female_capacity_2026',
                'assigned_batch'
            ]);
        });
    }
};
