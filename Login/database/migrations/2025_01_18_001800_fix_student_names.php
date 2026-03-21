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
        // Fix misspelled student names
        DB::table('pnph_users')->where('user_id', 11)->update(['user_fname' => 'Ricky']);
        DB::table('pnph_users')->where('user_id', 10)->update(['user_fname' => 'Eduard John']);
        
        // Add more name corrections as needed
        // You can add more corrections here following the same pattern:
        // DB::table('pnph_users')->where('user_id', X)->update(['user_fname' => 'Corrected Name']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the changes if needed
        DB::table('pnph_users')->where('user_id', 11)->update(['user_fname' => 'Rcky']);
        DB::table('pnph_users')->where('user_id', 10)->update(['user_fname' => 'Eduard Jhon']);
    }
};
