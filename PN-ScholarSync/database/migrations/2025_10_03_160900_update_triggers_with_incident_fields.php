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
        // Drop existing triggers first
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');

        // Recreate academic trigger with all incident fields
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

                -- Get Schedule category ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Check if educator marked as 'Not Excused' (for time_in - late return or absent from academic activities)
                IF (NEW.educator_consideration = 'Not Excused' 
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND (NEW.time_in_remark = 'late' OR NEW.time_in_remark = 'absent'))
                THEN
                    -- Find specific violation type based on remark type
                    IF NEW.time_in_remark = 'absent' THEN
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Academic absence without valid excuse.'
                        LIMIT 1;
                        
                        -- Set incident datetime for absence (default morning time)
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 08:00:00');
                    ELSE
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Late academic login/logout.'
                        LIMIT 1;
                        
                        -- Set incident datetime for late (default morning time)
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 08:00:00');
                    END IF;

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        IF NEW.time_in_remark = 'absent' THEN
                            INSERT INTO violation_types (
                                offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                            ) VALUES (
                                v_schedule_category_id, 
                                'Academic absence without valid excuse.', 
                                'Student was absent from academic activities without valid excuse', 
                                'VW', NOW(), NOW()
                            );
                        ELSE
                            INSERT INTO violation_types (
                                offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                            ) VALUES (
                                v_schedule_category_id, 
                                'Late academic login/logout.', 
                                'Student was late for academic login/logout without valid excuse', 
                                'VW', NOW(), NOW()
                            );
                        END IF;
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
                    IF NEW.time_in_remark = 'absent' THEN
                        SET v_incident_details = CONCAT('Absent from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    ELSE
                        SET v_incident_details = CONCAT('Late return from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    END IF;
                    
                    -- Insert violation record with all incident fields
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.academic_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Check if time_out_consideration marked as 'Not Excused' (for time_out - late departure or absent)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND (NEW.time_out_remark = 'late' OR NEW.time_out_remark = 'absent'))
                THEN
                    -- Find specific violation type based on remark type
                    IF NEW.time_out_remark = 'absent' THEN
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Academic absence without valid excuse.'
                        LIMIT 1;
                        
                        -- Set incident datetime for absence (default evening time)
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 17:00:00');
                    ELSE
                        SELECT id INTO v_violation_type_id 
                        FROM violation_types 
                        WHERE violation_name = 'Late academic login/logout.'
                        LIMIT 1;
                        
                        -- Set incident datetime for late (default evening time)
                        SET v_incident_datetime = CONCAT(NEW.academic_date, ' 17:00:00');
                    END IF;

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        IF NEW.time_out_remark = 'absent' THEN
                            INSERT INTO violation_types (
                                offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                            ) VALUES (
                                v_schedule_category_id, 
                                'Academic absence without valid excuse.', 
                                'Student was absent from academic activities without valid excuse', 
                                'VW', NOW(), NOW()
                            );
                        ELSE
                            INSERT INTO violation_types (
                                offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                            ) VALUES (
                                v_schedule_category_id, 
                                'Late academic login/logout.', 
                                'Student was late for academic login/logout without valid excuse', 
                                'VW', NOW(), NOW()
                            );
                        END IF;
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
                    IF NEW.time_out_remark = 'absent' THEN
                        SET v_incident_details = CONCAT('Absent from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    ELSE
                        SET v_incident_details = CONCAT('Late departure from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'), '. Student reason: ', v_reason);
                    END IF;
                    
                    -- Insert violation record with all incident fields
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.academic_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('academic_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        // Recreate going out trigger with all incident fields
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

                -- Get Schedule category ID
                SELECT id INTO v_schedule_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;

                -- Check if educator marked as 'Not Excused' (for time_in - late return from going out)
                IF (NEW.educator_consideration = 'Not Excused' 
                    AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
                    AND NEW.time_in_remark = 'late')
                THEN
                    -- Find specific violation type for late going out
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going out login/logout.'
                    LIMIT 1;

                    -- Set incident datetime (default noon time for going out return)
                    SET v_incident_datetime = CONCAT(NEW.going_out_date, ' 12:00:00');

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late going out login/logout.', 
                            'Student was late for going out login/logout without valid excuse', 
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

                    -- Prepare incident details and reason
                    SET v_reason = COALESCE(NEW.time_in_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late return from going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record with all incident fields
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.going_out_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;

                -- Check if time_out_consideration marked as 'Not Excused' (for time_out - late departure)
                IF (NEW.time_out_consideration = 'Not Excused' 
                    AND (OLD.time_out_consideration IS NULL OR OLD.time_out_consideration != 'Not Excused')
                    AND NEW.time_out_remark = 'late')
                THEN
                    -- Find specific violation type for late going out
                    SELECT id INTO v_violation_type_id 
                    FROM violation_types 
                    WHERE violation_name = 'Late going out login/logout.'
                    LIMIT 1;

                    -- Set incident datetime (default evening time for going out departure)
                    SET v_incident_datetime = CONCAT(NEW.going_out_date, ' 18:00:00');

                    -- If specific violation type not found, create it
                    IF v_violation_type_id IS NULL THEN
                        INSERT INTO violation_types (
                            offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                        ) VALUES (
                            v_schedule_category_id, 
                            'Late going out login/logout.', 
                            'Student was late for going out login/logout without valid excuse', 
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

                    -- Prepare incident details and reason
                    SET v_reason = COALESCE(NEW.time_out_reason, 'No reason provided');
                    SET v_incident_details = CONCAT('Late departure for going out on ', DATE_FORMAT(NEW.going_out_date, '%Y-%m-%d'), ' - Destination: ', COALESCE(NEW.destination, 'Not specified'), '. Student reason: ', v_reason);
                    
                    -- Insert violation record with all incident fields
                    INSERT INTO violations (
                        student_id, violation_type_id, severity, violation_date, 
                        penalty, consequence, incident_details, status, action_taken, consequence_status,
                        incident_datetime, place_of_incident, prepared_by, offense_count,
                        logify_sync_batch_id, created_at, updated_at
                    ) VALUES (
                        NEW.student_id, v_violation_type_id, 'Low', NEW.going_out_date,
                        v_penalty, 'Pending educator review', v_incident_details, 'active', 1, 'pending',
                        v_incident_datetime, 'PN-PH Center', 'Logify System', v_offense_count,
                        CONCAT('going_out_', NEW.id, '_', UNIX_TIMESTAMP()), NOW(), NOW()
                    );
                END IF;
            END
        ");

        echo 'Triggers updated successfully with all incident fields!' . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_academic_validation;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_going_out_validation;');
        
        echo 'Triggers dropped successfully!' . PHP_EOL;
    }
};
