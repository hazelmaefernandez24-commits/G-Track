<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Add occupant_type column to rooms table
     * occupant_type: 'male', 'female', or 'both' (null defaults to 'both')
     */
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('rooms', 'occupant_type')) {
                $table->enum('occupant_type', ['male', 'female', 'both'])
                    ->default('both')
                    ->comment('Room occupant type: male, female, or both occupants')
                    ->after('assigned_batch');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            if (Schema::hasColumn('rooms', 'occupant_type')) {
                $table->dropColumn('occupant_type');
            }
        });
    }
};
