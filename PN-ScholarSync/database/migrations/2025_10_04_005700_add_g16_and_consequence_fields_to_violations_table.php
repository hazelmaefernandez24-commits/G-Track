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
            // Link to G16 task_submissions
            if (!Schema::hasColumn('violations', 'task_submission_id')) {
                $table->unsignedBigInteger('task_submission_id')->nullable()->after('id');
                $table->index('task_submission_id', 'violations_task_submission_idx');
            }

            // Offense (short label used by integration service)
            if (!Schema::hasColumn('violations', 'offense')) {
                $table->string('offense')->nullable()->after('violation_type_id');
            }

            // Incident context
            if (!Schema::hasColumn('violations', 'incident_details')) {
                $table->text('incident_details')->nullable()->after('consequence');
            }
            if (!Schema::hasColumn('violations', 'prepared_by')) {
                $table->string('prepared_by')->nullable()->after('incident_details');
            }

            // Consequence workflow fields (separate from main status)
            if (!Schema::hasColumn('violations', 'consequence_status')) {
                $table->enum('consequence_status', ['pending', 'active', 'resolved'])->nullable()->default('pending')->after('status');
            }
            if (!Schema::hasColumn('violations', 'action_taken')) {
                $table->boolean('action_taken')->default(false)->after('consequence_status');
            }
            if (!Schema::hasColumn('violations', 'consequence_start_date')) {
                $table->date('consequence_start_date')->nullable()->after('action_taken');
            }
            if (!Schema::hasColumn('violations', 'consequence_end_date')) {
                $table->date('consequence_end_date')->nullable()->after('consequence_start_date');
            }
            if (!Schema::hasColumn('violations', 'consequence_duration_value')) {
                $table->integer('consequence_duration_value')->nullable()->after('consequence_end_date');
            }
            if (!Schema::hasColumn('violations', 'consequence_duration_unit')) {
                $table->enum('consequence_duration_unit', ['days', 'weeks', 'months'])->nullable()->after('consequence_duration_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('violations', 'consequence_duration_unit')) {
                $table->dropColumn('consequence_duration_unit');
            }
            if (Schema::hasColumn('violations', 'consequence_duration_value')) {
                $table->dropColumn('consequence_duration_value');
            }
            if (Schema::hasColumn('violations', 'consequence_end_date')) {
                $table->dropColumn('consequence_end_date');
            }
            if (Schema::hasColumn('violations', 'consequence_start_date')) {
                $table->dropColumn('consequence_start_date');
            }
            if (Schema::hasColumn('violations', 'action_taken')) {
                $table->dropColumn('action_taken');
            }
            if (Schema::hasColumn('violations', 'consequence_status')) {
                $table->dropColumn('consequence_status');
            }
            if (Schema::hasColumn('violations', 'prepared_by')) {
                $table->dropColumn('prepared_by');
            }
            if (Schema::hasColumn('violations', 'incident_details')) {
                $table->dropColumn('incident_details');
            }
            if (Schema::hasColumn('violations', 'offense')) {
                $table->dropColumn('offense');
            }
            if (Schema::hasColumn('violations', 'task_submission_id')) {
                $table->dropIndex('violations_task_submission_idx');
                $table->dropColumn('task_submission_id');
            }
        });
    }
};
