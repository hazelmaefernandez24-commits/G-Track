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
        // Create trigger for intern_log table
        DB::unprepared("
            CREATE TRIGGER tr_after_intern_log_validation
            AFTER UPDATE ON intern_log
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;
                DECLARE v_offense_count INT DEFAULT 1;
                DECLARE v_penalty VARCHAR(10) DEFAULT 'VW';
                DECLARE v_incident_datetime DATETIME;

                -- Get Schedule category ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Check for INTERN ABSENCE: time_out_consideration = 'Not Excused' (only process once to avoid duplicates)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_in_remark = 'absent' AND NEW.time_out_remark = 'absent')
                THEN
                    -- Find intern absence violation type
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Intern absence without valid excuse.'
                    LIMIT 1;
                    
                    -- Set incident datetime for absence (use expected or default morning time)
                    SET v_incident_datetime = CONCAT(NEW.date, ' 08:00:00');

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Intern absence without valid excuse.', 
                            'Student was absent from internship activities without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Calculate offense count for penalty escalation
                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    -- Determine penalty based on offense count (VW → WW → Pro → T)
                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    -- Prepare incident details
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Absent from internship activities on ', DATE_FORMAT(NEW.date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record for intern absence
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'Internship Site', 'Logify System', v_offense_count,
                        CONCAT('intern_absent_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Note: intern_log table only has time_out_consideration, not time_in_consideration
                -- Late time_in violations are handled through time_out_consideration when time_in_remark = 'late'

                -- Check for INTERN LATE: time_out_consideration = 'Not Excused' for time_out (late departure)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remark = 'late')
                THEN
                    -- Find intern late violation type
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late intern login/logout.'
                    LIMIT 1;
                    
                    -- Set incident datetime for late (use actual time_out or default)
                    SET v_incident_datetime = CONCAT(NEW.date, ' ', COALESCE(NEW.time_out, '17:00:00'));

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late intern login/logout.', 
                            'Student was late for internship login/logout without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Calculate offense count for penalty escalation
                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    -- Determine penalty based on offense count (VW → WW → Pro → T)
                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    -- Prepare incident details
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late departure from internship on ', DATE_FORMAT(NEW.date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record for intern late departure
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'Internship Site', 'Logify System', v_offense_count,
                        CONCAT('intern_late_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        // Create trigger for going_home table
        DB::unprepared("
            CREATE TRIGGER tr_after_going_home_validation
            AFTER UPDATE ON going_home
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;
                DECLARE v_offense_count INT DEFAULT 1;
                DECLARE v_penalty VARCHAR(10) DEFAULT 'VW';
                DECLARE v_incident_datetime DATETIME;

                -- Get Schedule category ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Check for GOING HOME LATE: time_in_consideration = 'Not Excused' for time_in (late return)
                IF (NEW.time_in_consideration = 'Not Excused' 
                    AND (OLD.time_in_consideration IS NULL OR OLD.time_in_consideration != 'Not Excused')
                    AND NEW.time_in_remarks = 'late')
                THEN
                    -- Find going home late violation type
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going home login.'
                    LIMIT 1;
                    
                    -- Set incident datetime for late (use actual time_in or default)
                    SET v_incident_datetime = CONCAT(DATE(NEW.date_time_in), ' ', COALESCE(NEW.time_in, '18:00:00'));

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late going home login.', 
                            'Student was late returning from going home without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Calculate offense count for penalty escalation
                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    -- Determine penalty based on offense count (VW → WW → Pro → T)
                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    -- Prepare incident details
                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late return from going home on ', DATE_FORMAT(DATE(NEW.date_time_in), '%Y-%m-%d'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record for going home late
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', DATE(NEW.date_time_in),
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_home_late_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Check for GOING HOME LATE: time_out_consideration = 'Not Excused' for time_out (late departure)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remarks = 'late')
                THEN
                    -- Find going home late violation type
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going home login.'
                    LIMIT 1;
                    
                    -- Set incident datetime for late (use actual time_out or default)
                    SET v_incident_datetime = CONCAT(DATE(NEW.date_time_out), ' ', COALESCE(NEW.time_out, '17:00:00'));

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late going home login.', 
                            'Student was late for going home departure without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Calculate offense count for penalty escalation
                    SELECT COUNT(*) + 1 INTO v_offense_count
                    FROM violations 
                    WHERE student_id = NEW.student_id 
                    AND violation_type_id = v_violation_type_id
                    AND action_taken = 1
                    AND status != 'appeal_approved';

                    -- Determine penalty based on offense count (VW → WW → Pro → T)
                    CASE v_offense_count
                        WHEN 1 THEN SET v_penalty = 'VW';
                        WHEN 2 THEN SET v_penalty = 'WW';
                        WHEN 3 THEN SET v_penalty = 'Pro';
                        ELSE SET v_penalty = 'T';
                    END CASE;

                    -- Prepare incident details
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late departure for going home on ', DATE_FORMAT(DATE(NEW.date_time_out), '%Y-%m-%d'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record for going home late departure
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', DATE(NEW.date_time_out),
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_home_late_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        echo 'Intern log and going home triggers created successfully!' . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_intern_log_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_home_validation;');
        
        echo 'Intern log and going home triggers dropped successfully!' . PHP_EOL;
    }
};
