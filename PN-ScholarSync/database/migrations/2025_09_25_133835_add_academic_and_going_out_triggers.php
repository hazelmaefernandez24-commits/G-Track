<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing triggers if they already exist
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');

        // Trigger for academics table
        DB::unprepared("
            CREATE TRIGGER tr_after_academic_validation
            AFTER UPDATE ON academics
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_low_severity_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;

                -- Get Schedule category ID and Low severity ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;
                SELECT id INTO v_low_severity_id FROM severities WHERE severity_name = 'Low' LIMIT 1;

                -- Check if educator marked as 'Not Excused' (for time_in - late return from academic activities)
                IF (NEW.educator_consideration = 'Not Excused' 
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND NEW.time_in_remark = 'late')
                THEN
                    -- Find existing late/absent violation type from Schedule category
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE offense_category_id = v_schedule_category_id 
                    AND (violation_name LIKE '%late%' OR violation_name LIKE '%absent%' OR violation_name LIKE '%schedule%')
                    LIMIT 1;

                    -- If no existing violation type found, create a generic late violation
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late return from academic activities', 
                            'Student returned late from academic activities without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Prepare incident details and reason
                    SET v_incident_details = CONCAT('Late return from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'));
                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    
                    -- Insert violation record
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, 
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'low', NEW.academic_date,
                        'VW', v_reason, v_incident_details, 'active',
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Check if time_out_consideration marked as 'Not Excused' (for time_out - late departure)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remark = 'late')
                THEN
                    -- Find existing late/absent violation type from Schedule category
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE offense_category_id = v_schedule_category_id 
                    AND (violation_name LIKE '%late%' OR violation_name LIKE '%absent%' OR violation_name LIKE '%schedule%')
                    LIMIT 1;

                    -- If no existing violation type found, create a generic late violation
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late departure from academic activities', 
                            'Student departed late from academic activities without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Prepare incident details and reason
                    SET v_incident_details = CONCAT('Late departure from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'));
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    
                    -- Insert violation record
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, 
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'low', NEW.academic_date,
                        'VW', v_reason, v_incident_details, 'active',
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        // Trigger for going_outs table
        DB::unprepared("
            CREATE TRIGGER tr_after_going_out_validation
            AFTER UPDATE ON going_outs
            FOR EACH ROW
            BEGIN
                DECLARE v_violation_type_id INT;
                DECLARE v_schedule_category_id INT;
                DECLARE v_low_severity_id INT;
                DECLARE v_incident_details TEXT;
                DECLARE v_reason TEXT;

                -- Get Schedule category ID and Low severity ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;
                SELECT id INTO v_low_severity_id FROM severities WHERE severity_name = 'Low' LIMIT 1;

                -- Check if educator marked as 'Not Excused' (for time_in - late return from going out)
                IF (NEW.educator_consideration = 'Not Excused' 
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND NEW.time_in_remark = 'late')
                THEN
                    -- Find existing late/absent violation type from Schedule category
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE offense_category_id = v_schedule_category_id 
                    AND (violation_name LIKE '%late%' OR violation_name LIKE '%absent%' OR violation_name LIKE '%schedule%')
                    LIMIT 1;

                    -- If no existing violation type found, create a generic late violation
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late return from going out', 
                            'Student returned late from going out without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Prepare incident details and reason
                    SET v_incident_details = CONCAT('Late return from going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'));
                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    
                    -- Insert violation record
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, 
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'low', NEW.going_out_date,
                        'VW', v_reason, v_incident_details, 'active',
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Check if time_out_consideration marked as 'Not Excused' (for time_out - late departure)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remark = 'late')
                THEN
                    -- Find existing late/absent violation type from Schedule category
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE offense_category_id = v_schedule_category_id 
                    AND (violation_name LIKE '%late%' OR violation_name LIKE '%absent%' OR violation_name LIKE '%schedule%')
                    LIMIT 1;

                    -- If no existing violation type found, create a generic late violation
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late departure for going out', 
                            'Student departed late for going out without valid excuse', 
                            'VW', NOW(), NOW()
                        );
                        SET v_violation_type_id = LAST_INSERT_ID();
                    END IF;

                    -- Prepare incident details and reason
                    SET v_incident_details = CONCAT('Late departure for going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'));
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    
                    -- Insert violation record
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, 
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'low', NEW.going_out_date,
                        'VW', v_reason, v_incident_details, 'active',
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');
    }
};
