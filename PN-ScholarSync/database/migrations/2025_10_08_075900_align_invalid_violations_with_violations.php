<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invalid_violations', function (Blueprint $table) {
            // Soft deletes to match violations
            if (!Schema::hasColumn('invalid_violations', 'deleted_at')) {
                $table->softDeletes();
            }

            // Ensure indexes similar to violations
            if (!Schema::hasColumn('invalid_violations', 'violation_date')) {
                $table->date('violation_date')->nullable();
            }
            // Add index if not present
            $table->index('violation_date');

            if (!Schema::hasColumn('invalid_violations', 'status')) {
                $table->string('status', 50)->default('active');
            }
            $table->index('status');

            // Add missing columns present in violations
            if (!Schema::hasColumn('invalid_violations', 'infraction_count')) {
                $table->integer('infraction_count')->nullable()->after('prepared_by');
            }

            if (!Schema::hasColumn('invalid_violations', 'recorded_by')) {
                $table->string('recorded_by')->nullable()->after('consequence');
            }

            if (!Schema::hasColumn('invalid_violations', 'consequence_duration_value')) {
                $table->integer('consequence_duration_value')->nullable()->after('consequence');
            }
            if (!Schema::hasColumn('invalid_violations', 'consequence_duration_unit')) {
                $table->enum('consequence_duration_unit', ['hours', 'days', 'weeks', 'months'])->nullable()->after('consequence_duration_value');
            }
            if (!Schema::hasColumn('invalid_violations', 'consequence_start_date')) {
                $table->timestamp('consequence_start_date')->nullable()->after('consequence_duration_unit');
            }
            if (!Schema::hasColumn('invalid_violations', 'consequence_end_date')) {
                $table->timestamp('consequence_end_date')->nullable()->after('consequence_start_date');
            }
            if (!Schema::hasColumn('invalid_violations', 'consequence_status')) {
                $table->enum('consequence_status', ['pending', 'active', 'resolved'])->default('pending')->after('consequence_end_date');
            }

            // Align types/lengths where safe without doctrine/dbal
            // penalty should be string(50) in violations; ensure column exists; if exists assume compatible
            if (!Schema::hasColumn('invalid_violations', 'penalty')) {
                $table->string('penalty', 50)->nullable()->after('severity');
            }

            // offense present in violations; ensure present here
            if (!Schema::hasColumn('invalid_violations', 'offense')) {
                $table->string('offense', 100)->nullable()->after('violation_type_id');
            }

            // incident_datetime, incident_place, incident_details, prepared_by already exist in invalid_violations per creation
            // Ensure they exist (add if missing)
            if (!Schema::hasColumn('invalid_violations', 'incident_datetime')) {
                $table->dateTime('incident_datetime')->nullable()->after('violation_date');
            }
            if (!Schema::hasColumn('invalid_violations', 'incident_place')) {
                $table->string('incident_place', 191)->nullable()->after('incident_datetime');
            }
            if (!Schema::hasColumn('invalid_violations', 'incident_details')) {
                $table->text('incident_details')->nullable()->after('incident_place');
            }
            if (!Schema::hasColumn('invalid_violations', 'prepared_by')) {
                $table->string('prepared_by', 191)->nullable()->after('incident_details');
            }
        });

        // Adjust defaults to match violations where needed using raw SQL to avoid doctrine/dbal
        // consequence_status should default to 'pending'
        try {
            DB::statement("ALTER TABLE invalid_violations MODIFY COLUMN consequence_status ENUM('pending','active','resolved') DEFAULT 'pending'");
        } catch (\Throwable $e) {
            // ignore if column does not exist or platform doesn't support this exact statement
        }

        // action_taken default true in violations; ensure same here
        try {
            DB::statement("ALTER TABLE invalid_violations MODIFY COLUMN action_taken TINYINT(1) DEFAULT 1");
        } catch (\Throwable $e) {
            // ignore if cannot modify
        }

        // Add foreign key for recorded_by if possible (optional, non-breaking)
        try {
            Schema::table('invalid_violations', function (Blueprint $table) {
                if (Schema::hasColumn('invalid_violations', 'recorded_by')) {
                    // Add foreign key only if not already exists; Laravel cannot easily check FK name, so wrap in try/catch above if needed
                    $table->foreign('recorded_by')->references('user_id')->on('pnph_users');
                }
            });
        } catch (\Throwable $e) {
            // ignore FK creation issues
        }
    }

    public function down(): void
    {
        Schema::table('invalid_violations', function (Blueprint $table) {
            // Drop columns we added if present
            if (Schema::hasColumn('invalid_violations', 'infraction_count')) {
                $table->dropColumn('infraction_count');
            }
            if (Schema::hasColumn('invalid_violations', 'recorded_by')) {
                // Drop FK if exists (best-effort)
                try { $table->dropForeign(['recorded_by']); } catch (\Throwable $e) {}
                $table->dropColumn('recorded_by');
            }
            if (Schema::hasColumn('invalid_violations', 'consequence_duration_value')) {
                $table->dropColumn('consequence_duration_value');
            }
            if (Schema::hasColumn('invalid_violations', 'consequence_duration_unit')) {
                $table->dropColumn('consequence_duration_unit');
            }
            if (Schema::hasColumn('invalid_violations', 'consequence_start_date')) {
                $table->dropColumn('consequence_start_date');
            }
            if (Schema::hasColumn('invalid_violations', 'consequence_end_date')) {
                $table->dropColumn('consequence_end_date');
            }
            // Do not drop 'consequence_status' entirely, but we won't revert enum default via schema here
            // Keep indexes as is; dropping may affect performance expectations

            if (Schema::hasColumn('invalid_violations', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        // Best-effort revert defaults using raw SQL
        try {
            DB::statement("ALTER TABLE invalid_violations MODIFY COLUMN consequence_status VARCHAR(50) DEFAULT 'active'");
        } catch (\Throwable $e) {}
        try {
            DB::statement("ALTER TABLE invalid_violations MODIFY COLUMN action_taken TINYINT(1) DEFAULT 1");
        } catch (\Throwable $e) {}
    }
};
