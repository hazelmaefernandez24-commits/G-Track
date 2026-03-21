<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, expand the enums to include all possible values
        DB::statement("ALTER TABLE violations MODIFY COLUMN penalty ENUM('V', 'W', 'VW', 'WW', 'P', 'Pro', 'Exp', 'T')");
        DB::statement("ALTER TABLE violation_types MODIFY COLUMN default_penalty ENUM('V', 'W', 'VW', 'WW', 'P', 'Pro', 'Exp', 'T')");

        // Update violations table legacy codes
        DB::table('violations')->where('penalty', 'V')->update(['penalty' => 'VW']);
        DB::table('violations')->where('penalty', 'P')->update(['penalty' => 'Pro']);
        DB::table('violations')->where('penalty', 'W')->update(['penalty' => 'WW']);
        DB::table('violations')->where('penalty', 'Exp')->update(['penalty' => 'T']);

        // Update violation_types table legacy codes
        DB::table('violation_types')->where('default_penalty', 'V')->update(['default_penalty' => 'VW']);
        DB::table('violation_types')->where('default_penalty', 'P')->update(['default_penalty' => 'Pro']);
        DB::table('violation_types')->where('default_penalty', 'W')->update(['default_penalty' => 'WW']);
        DB::table('violation_types')->where('default_penalty', 'Exp')->update(['default_penalty' => 'T']);

        // Finally, restrict the enums to only include the new penalty codes
        DB::statement("ALTER TABLE violations MODIFY COLUMN penalty ENUM('VW', 'WW', 'Pro', 'T') COMMENT 'VW=Verbal Warning, WW=Written Warning, Pro=Probation, T=Termination of Contract'");
        DB::statement("ALTER TABLE violation_types MODIFY COLUMN default_penalty ENUM('VW', 'WW', 'Pro', 'T') COMMENT 'VW=Verbal Warning, WW=Written Warning, Pro=Probation, T=Termination of Contract'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert penalty codes back to legacy values
        DB::table('violations')->where('penalty', 'VW')->update(['penalty' => 'V']);
        DB::table('violations')->where('penalty', 'Pro')->update(['penalty' => 'P']);
        DB::table('violations')->where('penalty', 'WW')->update(['penalty' => 'W']);
        DB::table('violations')->where('penalty', 'T')->update(['penalty' => 'Exp']);

        DB::table('violation_types')->where('default_penalty', 'VW')->update(['default_penalty' => 'V']);
        DB::table('violation_types')->where('default_penalty', 'Pro')->update(['default_penalty' => 'P']);
        DB::table('violation_types')->where('default_penalty', 'WW')->update(['default_penalty' => 'W']);
        DB::table('violation_types')->where('default_penalty', 'T')->update(['default_penalty' => 'Exp']);

        // Revert enum to include legacy values
        DB::statement("ALTER TABLE violations MODIFY COLUMN penalty ENUM('V', 'W', 'VW', 'WW', 'P', 'Pro', 'Exp', 'T')");
        DB::statement("ALTER TABLE violation_types MODIFY COLUMN default_penalty ENUM('V', 'W', 'VW', 'WW', 'P', 'Pro', 'Exp', 'T')");
    }
};
