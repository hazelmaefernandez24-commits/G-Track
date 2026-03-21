<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('invalid_violations')) {
            Schema::create('invalid_violations', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('task_submission_id')->nullable()->index();
                $table->string('student_id')->nullable()->index();
                $table->string('gender', 10)->nullable();
                $table->date('violation_date')->nullable();
                $table->unsignedBigInteger('violation_type_id')->nullable();
                $table->string('severity', 50)->nullable();
                $table->string('offense', 100)->nullable();
                $table->string('penalty', 50)->nullable();
                $table->text('consequence')->nullable();
                $table->string('incident_place', 191)->nullable();
                $table->dateTime('incident_datetime')->nullable();
                $table->text('incident_details')->nullable();
                $table->string('prepared_by', 191)->nullable();
                $table->string('status', 50)->default('active');
                $table->boolean('action_taken')->default(true);
                $table->string('consequence_status', 50)->default('active');
                $table->timestamps();
            });
        }

        // Optional one-time backfill from invalid_students if table exists
        if (Schema::hasTable('invalid_students')) {
            DB::unprepared(<<<SQL
            INSERT INTO invalid_violations (
              task_submission_id,
              student_id,
              gender,
              violation_date,
              violation_type_id,
              severity,
              offense,
              penalty,
              consequence,
              incident_place,
              incident_datetime,
              incident_details,
              prepared_by,
              status,
              action_taken,
              consequence_status,
              created_at,
              updated_at
            )
            SELECT
              i.g16_submission_id,
              i.student_id_code,
              i.gender,
              DATE(IFNULL(i.validated_at, NOW())),
              NULL,
              'Low',
              'g16_invalid',
              'VW',
              CONCAT('Invalid task submission for ', COALESCE(i.task_category, 'task')),
              COALESCE(i.task_category, 'task'),
              IFNULL(i.validated_at, NOW()),
              COALESCE(NULLIF(i.admin_notes, ''), i.description),
              'G16 Bridge',
              'active',
              1,
              'active',
              NOW(),
              NOW()
            FROM invalid_students i
            LEFT JOIN invalid_violations iv
              ON iv.task_submission_id = i.g16_submission_id
            WHERE iv.id IS NULL;
            SQL);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('invalid_violations');
    }
};
