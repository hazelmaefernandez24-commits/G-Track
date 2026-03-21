-- Simple Logify Triggers for ScholarSync Integration
-- Compatible with older MySQL versions

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS tr_academics_after_insert;
DROP TRIGGER IF EXISTS tr_academics_after_update;
DROP TRIGGER IF EXISTS tr_going_outs_after_insert;
DROP TRIGGER IF EXISTS tr_going_outs_after_update;

-- Simple trigger for academics table INSERT
DELIMITER $$
CREATE TRIGGER tr_academics_after_insert
AFTER INSERT ON academics
FOR EACH ROW
BEGIN
    -- Only process if not deleted and has late/absent status
    IF NEW.is_deleted = 0 THEN
        -- Check for late login
        IF NEW.time_in_remark = 'late' THEN
            INSERT INTO logify_late_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, login_late_count, logout_late_count, 
                going_out_late_count, total_late_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                1, 0, 0, 1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                login_late_count = login_late_count + 1,
                total_late_count = total_late_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
        
        -- Check for late logout
        IF NEW.time_out_remark = 'late' THEN
            INSERT INTO logify_late_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, login_late_count, logout_late_count, 
                going_out_late_count, total_late_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                0, 1, 0, 1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                logout_late_count = logout_late_count + 1,
                total_late_count = total_late_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
        
        -- Check for absent
        IF NEW.time_in_absent_validation = 1 OR NEW.time_out_absent_validation = 1 THEN
            INSERT INTO logify_absent_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, academic_absent_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                academic_absent_count = academic_absent_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
    END IF;
END$$
DELIMITER ;

-- Simple trigger for academics table UPDATE
DELIMITER $$
CREATE TRIGGER tr_academics_after_update
AFTER UPDATE ON academics
FOR EACH ROW
BEGIN
    -- Only process if not deleted and something relevant changed
    IF NEW.is_deleted = 0 AND (
        OLD.time_in_remark != NEW.time_in_remark OR
        OLD.time_out_remark != NEW.time_out_remark OR
        OLD.time_in_absent_validation != NEW.time_in_absent_validation OR
        OLD.time_out_absent_validation != NEW.time_out_absent_validation OR
        OLD.is_deleted != NEW.is_deleted
    ) THEN
        -- Check for late login
        IF NEW.time_in_remark = 'late' THEN
            INSERT INTO logify_late_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, login_late_count, logout_late_count, 
                going_out_late_count, total_late_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                1, 0, 0, 1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                login_late_count = login_late_count + 1,
                total_late_count = total_late_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
        
        -- Check for late logout
        IF NEW.time_out_remark = 'late' THEN
            INSERT INTO logify_late_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, login_late_count, logout_late_count, 
                going_out_late_count, total_late_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                0, 1, 0, 1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                logout_late_count = logout_late_count + 1,
                total_late_count = total_late_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
        
        -- Check for absent
        IF NEW.time_in_absent_validation = 1 OR NEW.time_out_absent_validation = 1 THEN
            INSERT INTO logify_absent_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, academic_absent_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.academic_date), 2, '0'),
                YEAR(NEW.academic_date),
                1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                academic_absent_count = academic_absent_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
    END IF;
END$$
DELIMITER ;

-- Simple trigger for going_outs table INSERT
DELIMITER $$
CREATE TRIGGER tr_going_outs_after_insert
AFTER INSERT ON going_outs
FOR EACH ROW
BEGIN
    -- Only process if not deleted and late
    IF NEW.is_deleted = 0 AND NEW.time_in_remark = 'late' THEN
        INSERT INTO logify_late_records (
            student_id, first_name, last_name, batch, `group`,
            month, year, login_late_count, logout_late_count, 
            going_out_late_count, total_late_count,
            sync_batch_id, last_synced_at, created_at, updated_at
        )
        SELECT 
            sd.student_id,
            u.user_fname,
            u.user_lname,
            sd.batch,
            sd.group,
            LPAD(MONTH(NEW.going_out_date), 2, '0'),
            YEAR(NEW.going_out_date),
            0, 0, 1, 1,
            CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
            NOW(), NOW(), NOW()
        FROM student_details sd
        JOIN pnph_users u ON sd.user_id = u.user_id
        WHERE sd.student_id = NEW.student_id
        ON DUPLICATE KEY UPDATE
            going_out_late_count = going_out_late_count + 1,
            total_late_count = total_late_count + 1,
            sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
            last_synced_at = NOW(),
            updated_at = NOW();
    END IF;
END$$
DELIMITER ;

-- Simple trigger for going_outs table UPDATE
DELIMITER $$
CREATE TRIGGER tr_going_outs_after_update
AFTER UPDATE ON going_outs
FOR EACH ROW
BEGIN
    -- Only process if not deleted and something relevant changed
    IF NEW.is_deleted = 0 AND (
        OLD.time_in_remark != NEW.time_in_remark OR
        OLD.is_deleted != NEW.is_deleted
    ) THEN
        -- Check for late
        IF NEW.time_in_remark = 'late' THEN
            INSERT INTO logify_late_records (
                student_id, first_name, last_name, batch, `group`,
                month, year, login_late_count, logout_late_count, 
                going_out_late_count, total_late_count,
                sync_batch_id, last_synced_at, created_at, updated_at
            )
            SELECT 
                sd.student_id,
                u.user_fname,
                u.user_lname,
                sd.batch,
                sd.group,
                LPAD(MONTH(NEW.going_out_date), 2, '0'),
                YEAR(NEW.going_out_date),
                0, 0, 1, 1,
                CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                NOW(), NOW(), NOW()
            FROM student_details sd
            JOIN pnph_users u ON sd.user_id = u.user_id
            WHERE sd.student_id = NEW.student_id
            ON DUPLICATE KEY UPDATE
                going_out_late_count = going_out_late_count + 1,
                total_late_count = total_late_count + 1,
                sync_batch_id = CONCAT('TRIGGER_', UNIX_TIMESTAMP(), '_', NEW.student_id),
                last_synced_at = NOW(),
                updated_at = NOW();
        END IF;
    END IF;
END$$
DELIMITER ;
