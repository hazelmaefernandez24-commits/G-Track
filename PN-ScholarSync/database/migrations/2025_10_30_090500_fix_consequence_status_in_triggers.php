<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure old versions are removed
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');

        // Recreate academic trigger with valid consequence_status ('active')
        DB::unprepared("
            CREATE TRIGGER tr_after_academic_validation
            AFTER UPDATE ON academics
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;
                DECLARE v_offense_count INT DEFAULT 1;
                DECLARE v_penalty VARCHAR(10) DEFAULT 'VW';
                DECLARE v_incident_datetime DATETIME;

                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Educator Not Excused for time_in (late or absent)
                IF (NEW.educator_consideration = 'Not Excused'
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND (NEW.time_in_remark = 'late' OR NEW.time_in_remark = 'absent'))
                THEN
                    IF NEW.time_in_remark = 'absent' THEN
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Academic absence without valid excuse.'
                        LIMIT 1;
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 08:00:00');
                    ELSE
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Late academic login/logout.'
                        LIMIT 1;
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' ', COALESCE(NEW.time_in, '08:00:00'));
                    END IF;

                    IF v_violation_type_id IS NULL THEN
                        IF NEW.time_in_remark = 'absent' THEN
                            INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                            VALUES (v_schedule_category_id, 'Academic absence without valid excuse.', 'Student was absent from academic activities without valid excuse', 'VW', NOW(), NOW());
                        ELSE
                            INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                            VALUES (v_schedule_category_id, 'Late academic login/logout.', 'Student was late for academic login/logout without valid excuse', 'VW', NOW(), NOW());
                        END IF;
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    IF NEW.time_in_remark = 'absent' THEN
                        SET v_incident_details = CONCAT('Absent from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    ELSE
                        SET v_incident_details = CONCAT('Late return from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    END IF;

                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date,
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.academic_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'active',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Not Excused for time_out (late or absent)
                IF (NEW.time_out_consideration = 'Not Excused'
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND (NEW.time_out_remark = 'late' OR NEW.time_out_remark = 'absent'))
                THEN
                    IF NEW.time_out_remark = 'absent' THEN
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Academic absence without valid excuse.'
                        LIMIT 1;
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 17:00:00');
                    ELSE
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Late academic login/logout.'
                        LIMIT 1;
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' ', COALESCE(NEW.time_out, '17:00:00'));
                    END IF;

                    IF v_violation_type_id IS NULL THEN
                        IF NEW.time_out_remark = 'absent' THEN
                            INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                            VALUES (v_schedule_category_id, 'Academic absence without valid excuse.', 'Student was absent from academic activities without valid excuse', 'VW', NOW(), NOW());
                        ELSE
                            INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                            VALUES (v_schedule_category_id, 'Late academic login/logout.', 'Student was late for academic login/logout without valid excuse', 'VW', NOW(), NOW());
                        END IF;
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    IF NEW.time_out_remark = 'absent' THEN
                        SET v_incident_details = CONCAT('Absent from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    ELSE
                        SET v_incident_details = CONCAT('Late departure from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    END IF;

                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date,
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.academic_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'active',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        // Recreate going_out trigger with valid consequence_status ('active')
        DB::unprepared("
            CREATE TRIGGER tr_after_going_out_validation
            AFTER UPDATE ON going_outs
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;
                DECLARE v_offense_count INT DEFAULT 1;
                DECLARE v_penalty VARCHAR(10) DEFAULT 'VW';
                DECLARE v_incident_datetime DATETIME;

                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Educator Not Excused for time_in (late)
                IF (NEW.educator_consideration = 'Not Excused'
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND NEW.time_in_remark = 'late')
                THEN
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going out login/logout.'
                    LIMIT 1;

                    SET v_incident_datetime = CONCAT(NEW.going_out_date, ' ', COALESCE(NEW.time_in, '12:00:00'));

                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                        VALUES (v_schedule_category_id, 'Late going out login/logout.', 'Student was late for going out login/logout without valid excuse', 'VW', NOW(), NOW());
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late return from going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'), '. Student reason: ', v_reason);

                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date,
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.going_out_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'active',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Not Excused for time_out (late)
                IF (NEW.time_out_consideration = 'Not Excused'
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remark = 'late')
                THEN
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going out login/logout.'
                    LIMIT 1;

                    SET v_incident_datetime = CONCAT(NEW.going_out_date, ' ', COALESCE(NEW.time_out, '18:00:00'));

                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (offense_category_id, violation_name, description, default_penalty, created_at, updated_at)
                        VALUES (v_schedule_category_id, 'Late going out login/logout.', 'Student was late for going out login/logout without valid excuse', 'VW', NOW(), NOW());
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late departure for going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'), '. Student reason: ', v_reason);

                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date,
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.going_out_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'active',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');
    }
};


