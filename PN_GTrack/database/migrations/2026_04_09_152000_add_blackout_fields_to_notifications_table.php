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
        Schema::table('notifications', function (Blueprint $table) {
            // Update type enum to include blackout
            // Note: In some databases, changing enum is tricky. 
            // We'll use a string for now if it's already an enum or just change it.
            $table->string('type')->change(); 
            
            // Add new columns for blackout alerts
            $table->integer('battery_level')->nullable();
            $table->string('signal_status')->nullable();
            $table->string('location')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['battery_level', 'signal_status', 'location']);
            // Reverting string back to enum might require more logic
        });
    }
};
