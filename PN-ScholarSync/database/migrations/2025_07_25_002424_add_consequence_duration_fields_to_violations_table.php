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
            $table->integer('consequence_duration_value')->nullable()->after('consequence')->comment('Duration value for the consequence');
            $table->enum('consequence_duration_unit', ['hours', 'days', 'weeks', 'months'])->nullable()->after('consequence_duration_value')->comment('Duration unit for the consequence');
            $table->timestamp('consequence_start_date')->nullable()->after('consequence_duration_unit')->comment('When the consequence started');
            $table->timestamp('consequence_end_date')->nullable()->after('consequence_start_date')->comment('When the consequence should end');
            $table->enum('consequence_status', ['pending', 'active', 'resolved'])->default('pending')->after('consequence_end_date')->comment('Status of the consequence');

            // Add index for automatic resolution queries
            $table->index(['consequence_status', 'consequence_end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex(['consequence_status', 'consequence_end_date']);
            $table->dropColumn([
                'consequence_duration_value',
                'consequence_duration_unit',
                'consequence_start_date',
                'consequence_end_date',
                'consequence_status'
            ]);
        });
    }
};
