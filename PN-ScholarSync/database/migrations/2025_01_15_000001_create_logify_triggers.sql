-- =====================================================
-- LOGIFY DATABASE TRIGGERS FOR SCHOLARSYNC INTEGRATION
-- =====================================================
-- This file contains MySQL triggers that automatically sync
-- student attendance data from Logify to ScholarSync in real-time
-- 
-- Tables monitored:
-- - academics (login/logout times, late/absent status)
-- - going_outs (going out login times, late status)
--
-- Target ScholarSync tables:
-- - logify_late_records
-- - logify_absent_records  
-- - violations
-- =====================================================

-- =====================================================
-- HELPER PROCEDURES
-- =====================================================

DELIMITER $$

-- Procedure to sync late records to ScholarSync
CREATE PROCEDURE SyncLateRecord(
    IN p_student_id VARCHAR(50),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_batch VARCHAR(20),
    IN p_group VARCHAR(10),
    IN p_month VARCHAR(2),
    IN p_year VARCHAR(4),
    IN p_login_late_count INT,
    IN p_logout_late_count INT,
    IN p_going_out_late_count INT,
    IN p_total_late_count INT
)
BEGIN
    DECLARE v_sync_batch_id VARCHAR(50);
    DECLARE v_existing_record_id INT DEFAULT NULL;
    
    -- Generate unique sync batch ID
    SET v_sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', p_student_id);
    
    -- Check if record already exists for this student/month/year
    SELECT id INTO v_existing_record_id 
    FROM logify_late_records 
    WHERE student_id = p_student_id 
    AND month = p_month 
    AND year = p_year;
    
    IF v_existing_record_id IS NOT NULL THEN
        -- Update existing record
        UPDATE logify_late_records 
        SET 
            first_name = p_first_name,
            last_name = p_last_name,
            batch = p_batch,
            `group` = p_group,
            login_late_count = p_login_late_count,
            logout_late_count = p_logout_late_count,
            total_late_count = p_total_late_count,
            sync_batch_id = v_sync_batch_id,
            last_synced_at = NOW(),
            updated_at = NOW()
        WHERE id = v_existing_record_id;
    ELSE
        -- Insert new record
        INSERT INTO logify_late_records (
            student_id, first_name, last_name, batch, `group`,
            month, year, login_late_count, logout_late_count, 
            going_out_late_count, total_late_count,
            sync_batch_id, last_synced_at, created_at, updated_at
        ) VALUES (
            p_student_id, p_first_name, p_last_name, p_batch, p_group,
            p_month, p_year, p_login_late_count, p_logout_late_count,
            p_going_out_late_count, p_total_late_count,
            v_sync_batch_id, NOW(), NOW(), NOW()
        );
    END IF;
    
    -- Create violations for new late incidents
    CALL CreateLateViolations(p_student_id, p_login_late_count, p_logout_late_count, p_going_out_late_count, v_sync_batch_id);
END$$

-- Procedure to sync absent records to ScholarSync
CREATE PROCEDURE SyncAbsentRecord(
    IN p_student_id VARCHAR(50),
    IN p_first_name VARCHAR(100),
    IN p_last_name VARCHAR(100),
    IN p_batch VARCHAR(20),
    IN p_group VARCHAR(10),
    IN p_month VARCHAR(2),
    IN p_year VARCHAR(4),
    IN p_absent_count INT
)
BEGIN
    DECLARE v_sync_batch_id VARCHAR(50);
    DECLARE v_existing_record_id INT DEFAULT NULL;
    
    -- Generate unique sync batch ID
    SET v_sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', p_student_id);
    
    -- Check if record already exists for this student/month/year
    SELECT id INTO v_existing_record_id 
    FROM logify_absent_records 
    WHERE student_id = p_student_id 
    AND month = p_month 
    AND year = p_year;
    
    IF v_existing_record_id IS NOT NULL THEN
        -- Update existing record
        UPDATE logify_absent_records 
        SET 
            first_name = p_first_name,
            last_name = p_last_name,
            batch = p_batch,
            `group` = p_group,
            academic_absent_count = p_absent_count,
            sync_batch_id = v_sync_batch_id,
            last_synced_at = NOW(),
            updated_at = NOW()
        WHERE id = v_existing_record_id;
    ELSE
        -- Insert new record
        INSERT INTO logify_absent_records (
            student_id, first_name, last_name, batch, `group`,
            month, year, academic_absent_count,
            sync_batch_id, last_synced_at, created_at, updated_at
        ) VALUES (
            p_student_id, p_first_name, p_last_name, p_batch, p_group,
            p_month, p_year, p_absent_count,
            v_sync_batch_id, NOW(), NOW(), NOW()
        );
    END IF;
    
    -- Create violations for new absent incidents
    CALL CreateAbsentViolations(p_student_id, p_absent_count, v_sync_batch_id);
END$$

-- Procedure to create violations for late incidents
CREATE PROCEDURE CreateLateViolations(
    IN p_student_id VARCHAR(50),
    IN p_login_late_count INT,
    IN p_logout_late_count INT,
    IN p_going_out_late_count INT,
    IN p_sync_batch_id VARCHAR(50)
)
BEGIN
    DECLARE v_academic_login_late_type_id INT;
    DECLARE v_academic_logout_late_type_id INT;
    DECLARE v_going_out_login_late_type_id INT;
    DECLARE v_current_month VARCHAR(2);
    DECLARE v_current_year VARCHAR(4);
    DECLARE v_existing_violations INT;
    DECLARE v_new_incidents INT;
    DECLARE v_i INT DEFAULT 1;
    
    -- Get current month and year
    SET v_current_month = LPAD(MONTH(NOW()), 2, '0');
    SET v_current_year = YEAR(NOW());
    
    -- Get violation type IDs
    SELECT id INTO v_academic_login_late_type_id FROM violation_types WHERE violation_name = 'Academic Login Late' LIMIT 1;
    SELECT id INTO v_academic_logout_late_type_id FROM violation_types WHERE violation_name = 'Academic Logout Late' LIMIT 1;
    SELECT id INTO v_going_out_login_late_type_id FROM violation_types WHERE violation_name = 'Going-out Login Late' LIMIT 1;
    
    -- Create Academic Login Late violations
    IF v_academic_login_late_type_id IS NOT NULL AND p_login_late_count > 0 THEN
        -- Count existing violations for this month/year
        SELECT COUNT(*) INTO v_existing_violations
        FROM violations 
        WHERE student_id = p_student_id 
        AND violation_type_id = v_academic_login_late_type_id
        AND logify_sync_batch_id IS NOT NULL
        AND YEAR(violation_date) = v_current_year
        AND MONTH(violation_date) = v_current_month;
        
        SET v_new_incidents = GREATEST(0, p_login_late_count - v_existing_violations);
        
        -- Create new violations
        WHILE v_i <= v_new_incidents DO
            INSERT INTO violations (
                student_id, violation_type_id, violation_date, incident_details,
                severity, status, consequence, penalty, logify_sync_batch_id,
                created_at, updated_at
            ) VALUES (
                p_student_id, v_academic_login_late_type_id, CURDATE(),
                CONCAT('Academic Login Late incident from Logify trigger - Month: ', v_current_month, '/', v_current_year, ', Total Count: ', p_login_late_count),
                (SELECT severity FROM violation_types WHERE id = v_academic_login_late_type_id),
                'active', 'To be assigned by educator',
                (SELECT default_penalty FROM violation_types WHERE id = v_academic_login_late_type_id),
                p_sync_batch_id, NOW(), NOW()
            );
            SET v_i = v_i + 1;
        END WHILE;
        SET v_i = 1;
    END IF;
    
    -- Create Academic Logout Late violations
    IF v_academic_logout_late_type_id IS NOT NULL AND p_logout_late_count > 0 THEN
        -- Count existing violations for this month/year
        SELECT COUNT(*) INTO v_existing_violations
        FROM violations 
        WHERE student_id = p_student_id 
        AND violation_type_id = v_academic_logout_late_type_id
        AND logify_sync_batch_id IS NOT NULL
        AND YEAR(violation_date) = v_current_year
        AND MONTH(violation_date) = v_current_month;
        
        SET v_new_incidents = GREATEST(0, p_logout_late_count - v_existing_violations);
        
        -- Create new violations
        WHILE v_i <= v_new_incidents DO
            INSERT INTO violations (
                student_id, violation_type_id, violation_date, incident_details,
                severity, status, consequence, penalty, logify_sync_batch_id,
                created_at, updated_at
            ) VALUES (
                p_student_id, v_academic_logout_late_type_id, CURDATE(),
                CONCAT('Academic Logout Late incident from Logify trigger - Month: ', v_current_month, '/', v_current_year, ', Total Count: ', p_logout_late_count),
                (SELECT severity FROM violation_types WHERE id = v_academic_logout_late_type_id),
                'active', 'To be assigned by educator',
                (SELECT default_penalty FROM violation_types WHERE id = v_academic_logout_late_type_id),
                p_sync_batch_id, NOW(), NOW()
            );
            SET v_i = v_i + 1;
        END WHILE;
        SET v_i = 1;
    END IF;
    
    -- Create Going-out Login Late violations
    IF v_going_out_login_late_type_id IS NOT NULL AND p_going_out_late_count > 0 THEN
        -- Count existing violations for this month/year
        SELECT COUNT(*) INTO v_existing_violations
        FROM violations 
        WHERE student_id = p_student_id 
        AND violation_type_id = v_going_out_login_late_type_id
        AND logify_sync_batch_id IS NOT NULL
        AND YEAR(violation_date) = v_current_year
        AND MONTH(violation_date) = v_current_month;
        
        SET v_new_incidents = GREATEST(0, p_going_out_late_count - v_existing_violations);
        
        -- Create new violations
        WHILE v_i <= v_new_incidents DO
            INSERT INTO violations (
                student_id, violation_type_id, violation_date, incident_details,
                severity, status, consequence, penalty, logify_sync_batch_id,
                created_at, updated_at
            ) VALUES (
                p_student_id, v_going_out_login_late_type_id, CURDATE(),
                CONCAT('Going-out Login Late incident from Logify trigger - Month: ', v_current_month, '/', v_current_year, ', Total Count: ', p_going_out_late_count),
                (SELECT severity FROM violation_types WHERE id = v_going_out_login_late_type_id),
                'active', 'To be assigned by educator',
                (SELECT default_penalty FROM violation_types WHERE id = v_going_out_login_late_type_id),
                p_sync_batch_id, NOW(), NOW()
            );
            SET v_i = v_i + 1;
        END WHILE;
    END IF;
END$$

-- Procedure to create violations for absent incidents
CREATE PROCEDURE CreateAbsentViolations(
    IN p_student_id VARCHAR(50),
    IN p_absent_count INT,
    IN p_sync_batch_id VARCHAR(50)
)
BEGIN
    DECLARE v_academic_absent_type_id INT;
    DECLARE v_current_month VARCHAR(2);
    DECLARE v_current_year VARCHAR(4);
    DECLARE v_existing_violations INT;
    DECLARE v_new_incidents INT;
    DECLARE v_i INT DEFAULT 1;
    
    -- Get current month and year
    SET v_current_month = LPAD(MONTH(NOW()), 2, '0');
    SET v_current_year = YEAR(NOW());
    
    -- Get violation type ID
    SELECT id INTO v_academic_absent_type_id FROM violation_types WHERE violation_name = 'Academic Absent' LIMIT 1;
    
    -- Create Academic Absent violations
    IF v_academic_absent_type_id IS NOT NULL AND p_absent_count > 0 THEN
        -- Count existing violations for this month/year
        SELECT COUNT(*) INTO v_existing_violations
        FROM violations 
        WHERE student_id = p_student_id 
        AND violation_type_id = v_academic_absent_type_id
        AND logify_sync_batch_id IS NOT NULL
        AND YEAR(violation_date) = v_current_year
        AND MONTH(violation_date) = v_current_month;
        
        SET v_new_incidents = GREATEST(0, p_absent_count - v_existing_violations);
        
        -- Create new violations
        WHILE v_i <= v_new_incidents DO
            INSERT INTO violations (
                student_id, violation_type_id, violation_date, incident_details,
                severity, status, consequence, penalty, logify_sync_batch_id,
                created_at, updated_at
            ) VALUES (
                p_student_id, v_academic_absent_type_id, CURDATE(),
                CONCAT('Academic Absent incident from Logify trigger - Month: ', v_current_month, '/', v_current_year, ', Total Count: ', p_absent_count),
                (SELECT severity FROM violation_types WHERE id = v_academic_absent_type_id),
                'active', 'To be assigned by educator',
                (SELECT default_penalty FROM violation_types WHERE id = v_academic_absent_type_id),
                p_sync_batch_id, NOW(), NOW()
            );
            SET v_i = v_i + 1;
        END WHILE;
    END IF;
END$$

DELIMITER ;

-- =====================================================
-- TRIGGERS FOR ACADEMICS TABLE
-- =====================================================

-- Trigger for INSERT on academics table
DELIMITER $$
CREATE TRIGGER tr_academics_after_insert
AFTER INSERT ON academics
FOR EACH ROW
BEGIN
    DECLARE v_student_id VARCHAR(50);
    DECLARE v_first_name VARCHAR(100);
    DECLARE v_last_name VARCHAR(100);
    DECLARE v_batch VARCHAR(20);
    DECLARE v_group VARCHAR(10);
    DECLARE v_month VARCHAR(2);
    DECLARE v_year VARCHAR(4);
    DECLARE v_login_late_count INT DEFAULT 0;
    DECLARE v_logout_late_count INT DEFAULT 0;
    DECLARE v_absent_count INT DEFAULT 0;
    DECLARE v_total_late_count INT DEFAULT 0;
    
    -- Only process if not deleted
    IF NEW.is_deleted = 0 THEN
        -- Get student details
        SELECT 
            sd.student_id,
            u.user_fname,
            u.user_lname,
            sd.batch,
            sd.group
        INTO 
            v_student_id,
            v_first_name,
            v_last_name,
            v_batch,
            v_group
        FROM student_details sd
        JOIN pnph_users u ON sd.user_id = u.user_id
        WHERE sd.student_id = NEW.student_id;
        
        -- Get month and year
        SET v_month = LPAD(MONTH(NEW.academic_date), 2, '0');
        SET v_year = YEAR(NEW.academic_date);
        
        -- Count late incidents for this student in this month/year
        SELECT 
            COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_out_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_in_absent_validation = 1 OR time_out_absent_validation = 1 THEN 1 END)
        INTO 
            v_login_late_count,
            v_logout_late_count,
            v_absent_count
        FROM academics 
        WHERE student_id = NEW.student_id 
        AND YEAR(academic_date) = v_year 
        AND MONTH(academic_date) = v_month
        AND is_deleted = 0;
        
        SET v_total_late_count = v_login_late_count + v_logout_late_count;
        
        -- Sync late records if there are late incidents
        IF v_total_late_count > 0 THEN
            CALL SyncLateRecord(
                v_student_id, v_first_name, v_last_name, v_batch, v_group,
                v_month, v_year, v_login_late_count, v_logout_late_count, 0, v_total_late_count
            );
        END IF;
        
        -- Sync absent records if there are absent incidents
        IF v_absent_count > 0 THEN
            CALL SyncAbsentRecord(
                v_student_id, v_first_name, v_last_name, v_batch, v_group,
                v_month, v_year, v_absent_count
            );
        END IF;
    END IF;
END$$
DELIMITER ;

-- Trigger for UPDATE on academics table
DELIMITER $$
CREATE TRIGGER tr_academics_after_update
AFTER UPDATE ON academics
FOR EACH ROW
BEGIN
    DECLARE v_student_id VARCHAR(50);
    DECLARE v_first_name VARCHAR(100);
    DECLARE v_last_name VARCHAR(100);
    DECLARE v_batch VARCHAR(20);
    DECLARE v_group VARCHAR(10);
    DECLARE v_month VARCHAR(2);
    DECLARE v_year VARCHAR(4);
    DECLARE v_login_late_count INT DEFAULT 0;
    DECLARE v_logout_late_count INT DEFAULT 0;
    DECLARE v_absent_count INT DEFAULT 0;
    DECLARE v_total_late_count INT DEFAULT 0;
    
    -- Only process if not deleted and something relevant changed
    IF NEW.is_deleted = 0 AND (
        OLD.time_in_remark != NEW.time_in_remark OR
        OLD.time_out_remark != NEW.time_out_remark OR
        OLD.time_in_absent_validation != NEW.time_in_absent_validation OR
        OLD.time_out_absent_validation != NEW.time_out_absent_validation OR
        OLD.is_deleted != NEW.is_deleted
    ) THEN
        -- Get student details
        SELECT 
            sd.student_id,
            u.user_fname,
            u.user_lname,
            sd.batch,
            sd.group
        INTO 
            v_student_id,
            v_first_name,
            v_last_name,
            v_batch,
            v_group
        FROM student_details sd
        JOIN pnph_users u ON sd.user_id = u.user_id
        WHERE sd.student_id = NEW.student_id;
        
        -- Get month and year
        SET v_month = LPAD(MONTH(NEW.academic_date), 2, '0');
        SET v_year = YEAR(NEW.academic_date);
        
        -- Count late incidents for this student in this month/year
        SELECT 
            COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_out_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_in_absent_validation = 1 OR time_out_absent_validation = 1 THEN 1 END)
        INTO 
            v_login_late_count,
            v_logout_late_count,
            v_absent_count
        FROM academics 
        WHERE student_id = NEW.student_id 
        AND YEAR(academic_date) = v_year 
        AND MONTH(academic_date) = v_month
        AND is_deleted = 0;
        
        SET v_total_late_count = v_login_late_count + v_logout_late_count;
        
        -- Sync late records if there are late incidents
        IF v_total_late_count > 0 THEN
            CALL SyncLateRecord(
                v_student_id, v_first_name, v_last_name, v_batch, v_group,
                v_month, v_year, v_login_late_count, v_logout_late_count, 0, v_total_late_count
            );
        END IF;
        
        -- Sync absent records if there are absent incidents
        IF v_absent_count > 0 THEN
            CALL SyncAbsentRecord(
                v_student_id, v_first_name, v_last_name, v_batch, v_group,
                v_month, v_year, v_absent_count
            );
        END IF;
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- TRIGGERS FOR GOING_OUTS TABLE
-- =====================================================

-- Trigger for INSERT on going_outs table
DELIMITER $$
CREATE TRIGGER tr_going_outs_after_insert
AFTER INSERT ON going_outs
FOR EACH ROW
BEGIN
    DECLARE v_student_id VARCHAR(50);
    DECLARE v_first_name VARCHAR(100);
    DECLARE v_last_name VARCHAR(100);
    DECLARE v_batch VARCHAR(20);
    DECLARE v_group VARCHAR(10);
    DECLARE v_month VARCHAR(2);
    DECLARE v_year VARCHAR(4);
    DECLARE v_going_out_late_count INT DEFAULT 0;
    DECLARE v_login_late_count INT DEFAULT 0;
    DECLARE v_logout_late_count INT DEFAULT 0;
    DECLARE v_total_late_count INT DEFAULT 0;
    
    -- Only process if not deleted and late
    IF NEW.is_deleted = 0 AND NEW.time_in_remark = 'late' THEN
        -- Get student details
        SELECT 
            sd.student_id,
            u.user_fname,
            u.user_lname,
            sd.batch,
            sd.group
        INTO 
            v_student_id,
            v_first_name,
            v_last_name,
            v_batch,
            v_group
        FROM student_details sd
        JOIN pnph_users u ON sd.user_id = u.user_id
        WHERE sd.student_id = NEW.student_id;
        
        -- Get month and year
        SET v_month = LPAD(MONTH(NEW.going_out_date), 2, '0');
        SET v_year = YEAR(NEW.going_out_date);
        
        -- Count going out late incidents for this student in this month/year
        SELECT COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END)
        INTO v_going_out_late_count
        FROM going_outs 
        WHERE student_id = NEW.student_id 
        AND YEAR(going_out_date) = v_year 
        AND MONTH(going_out_date) = v_month
        AND is_deleted = 0;
        
        -- Count academic late incidents for this student in this month/year
        SELECT 
            COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_out_remark = 'late' THEN 1 END)
        INTO 
            v_login_late_count,
            v_logout_late_count
        FROM academics 
        WHERE student_id = NEW.student_id 
        AND YEAR(academic_date) = v_year 
        AND MONTH(academic_date) = v_month
        AND is_deleted = 0;
        
        SET v_total_late_count = v_login_late_count + v_logout_late_count + v_going_out_late_count;
        
        -- Sync late records
        CALL SyncLateRecord(
            v_student_id, v_first_name, v_last_name, v_batch, v_group,
            v_month, v_year, v_login_late_count, v_logout_late_count, v_going_out_late_count, v_total_late_count
        );
    END IF;
END$$
DELIMITER ;

-- Trigger for UPDATE on going_outs table
DELIMITER $$
CREATE TRIGGER tr_going_outs_after_update
AFTER UPDATE ON going_outs
FOR EACH ROW
BEGIN
    DECLARE v_student_id VARCHAR(50);
    DECLARE v_first_name VARCHAR(100);
    DECLARE v_last_name VARCHAR(100);
    DECLARE v_batch VARCHAR(20);
    DECLARE v_group VARCHAR(10);
    DECLARE v_month VARCHAR(2);
    DECLARE v_year VARCHAR(4);
    DECLARE v_going_out_late_count INT DEFAULT 0;
    DECLARE v_login_late_count INT DEFAULT 0;
    DECLARE v_logout_late_count INT DEFAULT 0;
    DECLARE v_total_late_count INT DEFAULT 0;
    
    -- Only process if not deleted and something relevant changed
    IF NEW.is_deleted = 0 AND (
        OLD.time_in_remark != NEW.time_in_remark OR
        OLD.is_deleted != NEW.is_deleted
    ) THEN
        -- Get student details
        SELECT 
            sd.student_id,
            u.user_fname,
            u.user_lname,
            sd.batch,
            sd.group
        INTO 
            v_student_id,
            v_first_name,
            v_last_name,
            v_batch,
            v_group
        FROM student_details sd
        JOIN pnph_users u ON sd.user_id = u.user_id
        WHERE sd.student_id = NEW.student_id;
        
        -- Get month and year
        SET v_month = LPAD(MONTH(NEW.going_out_date), 2, '0');
        SET v_year = YEAR(NEW.going_out_date);
        
        -- Count going out late incidents for this student in this month/year
        SELECT COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END)
        INTO v_going_out_late_count
        FROM going_outs 
        WHERE student_id = NEW.student_id 
        AND YEAR(going_out_date) = v_year 
        AND MONTH(going_out_date) = v_month
        AND is_deleted = 0;
        
        -- Count academic late incidents for this student in this month/year
        SELECT 
            COUNT(CASE WHEN time_in_remark = 'late' THEN 1 END),
            COUNT(CASE WHEN time_out_remark = 'late' THEN 1 END)
        INTO 
            v_login_late_count,
            v_logout_late_count
        FROM academics 
        WHERE student_id = NEW.student_id 
        AND YEAR(academic_date) = v_year 
        AND MONTH(academic_date) = v_month
        AND is_deleted = 0;
        
        SET v_total_late_count = v_login_late_count + v_logout_late_count + v_going_out_late_count;
        
        -- Sync late records
        CALL SyncLateRecord(
            v_student_id, v_first_name, v_last_name, v_batch, v_group,
            v_month, v_year, v_login_late_count, v_logout_late_count, v_going_out_late_count, v_total_late_count
        );
    END IF;
END$$
DELIMITER ;

-- =====================================================
-- TRIGGER MANAGEMENT PROCEDURES
-- =====================================================

DELIMITER $$

-- Procedure to enable all Logify triggers
CREATE PROCEDURE EnableLogifyTriggers()
BEGIN
    -- Enable academics triggers
    ALTER TABLE academics ENABLE TRIGGER tr_academics_after_insert;
    ALTER TABLE academics ENABLE TRIGGER tr_academics_after_update;
    
    -- Enable going_outs triggers
    ALTER TABLE going_outs ENABLE TRIGGER tr_going_outs_after_insert;
    ALTER TABLE going_outs ENABLE TRIGGER tr_going_outs_after_update;
    
    SELECT 'All Logify triggers have been enabled' AS message;
END$$

-- Procedure to disable all Logify triggers
CREATE PROCEDURE DisableLogifyTriggers()
BEGIN
    -- Disable academics triggers
    ALTER TABLE academics DISABLE TRIGGER tr_academics_after_insert;
    ALTER TABLE academics DISABLE TRIGGER tr_academics_after_update;
    
    -- Disable going_outs triggers
    ALTER TABLE going_outs DISABLE TRIGGER tr_going_outs_after_insert;
    ALTER TABLE going_outs DISABLE TRIGGER tr_going_outs_after_update;
    
    SELECT 'All Logify triggers have been disabled' AS message;
END$$

-- Procedure to check trigger status
CREATE PROCEDURE CheckLogifyTriggerStatus()
BEGIN
    SELECT 
        TRIGGER_NAME,
        EVENT_MANIPULATION,
        EVENT_OBJECT_TABLE,
        TRIGGER_SCHEMA,
        STATUS
    FROM information_schema.TRIGGERS 
    WHERE TRIGGER_NAME LIKE 'tr_%logify%' 
    OR TRIGGER_NAME LIKE 'tr_academics%' 
    OR TRIGGER_NAME LIKE 'tr_going_outs%'
    ORDER BY EVENT_OBJECT_TABLE, EVENT_MANIPULATION;
END$$

DELIMITER ;

-- =====================================================
-- INSTALLATION INSTRUCTIONS
-- =====================================================
/*
To install these triggers:

1. Connect to your Logify MySQL database
2. Run this SQL file: mysql -u username -p logify_database < logify_triggers.sql
3. Verify installation: CALL CheckLogifyTriggerStatus();
4. Enable triggers: CALL EnableLogifyTriggers();

To test:
- Insert/update records in academics or going_outs tables
- Check logify_late_records and logify_absent_records tables in ScholarSync
- Check violations table for new violations

To disable triggers temporarily:
CALL DisableLogifyTriggers();

To re-enable:
CALL EnableLogifyTriggers();

To remove triggers:
DROP TRIGGER IF EXISTS tr_academics_after_insert;
DROP TRIGGER IF EXISTS tr_academics_after_update;
DROP TRIGGER IF EXISTS tr_going_outs_after_insert;
DROP TRIGGER IF EXISTS tr_going_outs_after_update;
DROP PROCEDURE IF EXISTS SyncLateRecord;
DROP PROCEDURE IF EXISTS SyncAbsentRecord;
DROP PROCEDURE IF EXISTS CreateLateViolations;
DROP PROCEDURE IF EXISTS CreateAbsentViolations;
DROP PROCEDURE IF EXISTS EnableLogifyTriggers;
DROP PROCEDURE IF EXISTS DisableLogifyTriggers;
DROP PROCEDURE IF EXISTS CheckLogifyTriggerStatus;
*/
