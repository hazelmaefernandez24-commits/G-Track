<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'pending' to the status enum for reports so the UI's "pending" value is accepted.
        // Using a raw statement for MySQL enum modification. If using another DB, adjust as needed.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `reports` MODIFY `status` ENUM('pending','active','resolved') NOT NULL DEFAULT 'active';");
        } else {
            // Fallback: try to alter using schema builder where supported
            Schema::table('reports', function (Blueprint $table) {
                $table->string('status')->default('active')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // WARNING: Down migration will fail if there are rows with status='pending'.
            DB::statement("ALTER TABLE `reports` MODIFY `status` ENUM('active','resolved') NOT NULL DEFAULT 'active';");
        } else {
            Schema::table('reports', function (Blueprint $table) {
                $table->enum('status', ['active','resolved'])->default('active')->change();
            });
        }
    }
};
