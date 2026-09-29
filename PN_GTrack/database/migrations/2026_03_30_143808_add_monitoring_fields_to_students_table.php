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
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'battery_level')) {
                $table->integer('battery_level')->default(100);
            }
            if (!Schema::hasColumn('students', 'signal_status')) {
                $table->string('signal_status')->default('Good');
            }
            if (!Schema::hasColumn('students', 'location')) {
                $table->string('location')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['battery_level', 'signal_status', 'location']);

        });
        
    }
};
