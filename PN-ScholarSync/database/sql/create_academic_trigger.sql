DELIMITER //

-- Drop the trigger if it exists
DROP TRIGGER IF EXISTS tr_after_academic_validation//

-- Create the trigger
CREATE TRIGGER tr_after_academic_validation
AFTER UPDATE ON academics
FOR EACH ROW
BEGIN
    DECLARE v_violation_type_id INT;
    DECLARE v_offense_count INT DEFAULT 1;
    DECLARE v_penalty VARCHAR(10) DEFAULT 'VW';
    DECLARE v_incident_datetime DATETIME;
    DECLARE v_incident_details TEXT;
    DECLARE v_category_id INT;
    
    -- Only proceed if marked as 'Not Excused' and either late or absent
    IF (NEW.educator_consideration = 'Not Excused' 
        AND (OLD.educator_consideration IS NULL OR OLD.educator_consideration != 'Not Excused')
        AND (NEW.time_in_remark = 'late' OR NEW.time_in_remark = 'absent'))
    THEN
        -- Set violation type based on remark
        IF NEW.time_in_remark = 'absent' THEN
            SELECT id INTO v_violation_type_id 
            FROM violation_types 
            WHERE violation_name = 'Academic absence without valid excuse.'
            LIMIT 1;
            
            SET v_incident_datetime = CONCAT(NEW.academic_date, ' ', COALESCE(NEW.expected_time_in, '08:00:00'));
            SET v_incident_details = CONCAT('Absent from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'));
        ELSE
            SELECT id INTO v_violation_type_id 
            FROM violation_types 
            WHERE violation_name = 'Late academic login/logout.'
            LIMIT 1;
            
            SET v_incident_datetime = CONCAT(NEW.academic_date, ' ', COALESCE(NEW.time_in, NEW.expected_time_in, '08:00:00'));
            SET v_incident_details = CONCAT('Late return from academic activities on ', DATE_FORMAT(NEW.academic_date, '%Y-%m-%d'));
        END IF;

        -- If violation type doesn't exist, create it
        IF v_violation_type_id IS NULL THEN
            -- Get Schedule category ID
            SELECT id INTO v_category_id FROM offense_categories WHERE category_name = 'Schedule' LIMIT 1;
            
            IF v_category_id IS NULL THEN
                INSERT INTO offense_categories (category_name, created_at, updated_at) 
                VALUES ('Schedule', NOW(), NOW());
                SET v_category_id = LAST_INSERT_ID();
            END IF;
            
            -- Create the violation type
            IF NEW.time_in_remark = 'absent' THEN
                INSERT INTO violation_types (
                    offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                ) VALUES (
                    v_category_id, 
                    'Academic absence without valid excuse.', 
                    'Student was absent from academic activities without valid excuse', 
                    'VW', NOW(), NOW()
                );
            ELSE
                INSERT INTO violation_types (
                    offense_category_id, violation_name, description, default_penalty, created_at, updated_at
                ) VALUES (
                    v_category_id, 
                    'Late academic login/logout.', 
                    'Student was late for academic login/logout without valid excuse', 
                    'VW', NOW(), NOW()
                );
            END IF;
            SET v_violation_type_id = LAST_INSERT_ID();
        END IF;

        -- Get offense count and penalty using the stored procedure
        CALL get_offense_count(NEW.student_id, v_violation_type_id, @offense_count, @penalty);
        SET v_offense_count = @offense_count;
        SET v_penalty = @penalty;
        
        -- Add reason to incident details if available
        IF NEW.time_in_reason IS NOT NULL THEN
            SET v_incident_details = CONCAT(v_incident_details, '. Reason: ', NEW.time_in_reason);
        ELSE
            SET v_incident_details = CONCAT(v_incident_details, '. No reason provided.');
        END IF;
        
        -- Insert the violation
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
END//

DELIMITER ;
