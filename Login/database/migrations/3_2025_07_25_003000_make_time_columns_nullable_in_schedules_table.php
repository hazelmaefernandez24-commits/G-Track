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
        Schema::table('schedules', function (Blueprint $table) {
            // Make time_out and time_in columns nullable
            $table->time('time_out')->nullable()->change();
            $table->time('time_in')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Revert time_out and time_in columns to NOT NULL
            $table->time('time_out')->nullable(false)->change();
            $table->time('time_in')->nullable(false)->change();
        });
    }
};
