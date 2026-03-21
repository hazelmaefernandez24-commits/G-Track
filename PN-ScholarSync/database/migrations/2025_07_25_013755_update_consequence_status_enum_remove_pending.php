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
        // First, update any remaining 'pending' status to appropriate values
        DB::table('violations')
            ->where('consequence_status', 'pending')
            ->update([
                'consequence_status' => DB::raw("CASE WHEN action_taken = 1 THEN 'active' ELSE 'resolved' END")
            ]);

        // Now modify the enum to only allow 'active' and 'resolved'
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_status ENUM('active', 'resolved') DEFAULT 'resolved'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original enum with 'pending'
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_status ENUM('pending', 'active', 'resolved') DEFAULT 'pending'");
    }
};
