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
        // For MySQL, we need to alter the enum to add 'minutes'
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_duration_unit ENUM('minutes', 'hours', 'days', 'weeks', 'months') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'minutes' from the enum
        DB::statement("ALTER TABLE violations MODIFY COLUMN consequence_duration_unit ENUM('hours', 'days', 'weeks', 'months') NULL");
    }
};
