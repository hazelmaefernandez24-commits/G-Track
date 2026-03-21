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
        Schema::table('violations', function (Blueprint $table) {
            // Add incident datetime field
            // $table->datetime('incident_datetime')->nullable()->after('violation_date');

            // Add place of incident field
            $table->string('place_of_incident', 255)->nullable()->after('incident_datetime');

            // Add prepared by field
            // $table->string('prepared_by', 255)->nullable()->after('place_of_incident');

            // Add offense count field to track escalation
            $table->integer('offense_count')->default(1)->after('prepared_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn([
                'incident_datetime',
                'place_of_incident',
                'prepared_by',
                'offense_count'
            ]);
        });
    }
};
