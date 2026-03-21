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
        // Update existing 'Exp' values to 'T' in violations table
        DB::table('violations')->where('penalty', 'Exp')->update(['penalty' => 'T']);

        // Update existing 'Exp' values to 'T' in violation_types table
        DB::table('violation_types')->where('default_penalty', 'Exp')->update(['default_penalty' => 'T']);

        // Modify violations table enum to remove 'Exp' and ensure 'T' is included
        DB::statement("ALTER TABLE violations MODIFY COLUMN penalty ENUM('W', 'VW', 'WW', 'Pro', 'T') COMMENT 'W=Warning, VW=Verbal Warning, WW=Written Warning, Pro=Probation, T=Termination of Contract'");

        // Modify violation_types table enum to remove 'Exp' and ensure 'T' is included
        DB::statement("ALTER TABLE violation_types MODIFY COLUMN default_penalty ENUM('W', 'VW', 'WW', 'Pro', 'T') COMMENT 'W=Warning, VW=Verbal Warning, WW=Written Warning, Pro=Probation, T=Termination of Contract'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to include 'Exp' in enum (for rollback purposes)
        DB::statement("ALTER TABLE violations MODIFY COLUMN penalty ENUM('W', 'VW', 'WW', 'Pro', 'Exp', 'T')");
        DB::statement("ALTER TABLE violation_types MODIFY COLUMN default_penalty ENUM('W', 'VW', 'WW', 'Pro', 'Exp', 'T')");
    }
};
