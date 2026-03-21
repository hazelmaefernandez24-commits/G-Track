-- Test script to verify triggers are working correctly
-- Run this after applying the migration

-- 1. Check if triggers exist
SHOW TRIGGERS LIKE 'academics';
SHOW TRIGGERS LIKE 'going_outs';

-- 2. Check existing violation types in Schedule category
SELECT vt.id, vt.violation_name, oc.category_name 
FROM violation_types vt 
JOIN offense_categories oc ON vt.offense_category_id = oc.id 
WHERE oc.category_name = 'Schedule' 
AND (vt.violation_name LIKE '%late%' OR vt.violation_name LIKE '%absent%' OR vt.violation_name LIKE '%schedule%');

-- 3. Check for any existing "Not Excused" records in academics
SELECT id, student_id, academic_date, time_in_remark, time_out_remark, 
       educator_consideration, time_out_consideration, time_in_reason, time_out_reason
FROM academics 
WHERE educator_consideration = 'Not Excused' OR time_out_consideration = 'Not Excused'
ORDER BY academic_date DESC 
LIMIT 5;

-- 4. Check for any existing "Not Excused" records in going_outs
SELECT id, student_id, going_out_date, time_in_remark, time_out_remark, 
       educator_consideration, time_out_consideration, time_in_reason, time_out_reason, destination
FROM going_outs 
WHERE educator_consideration = 'Not Excused' OR time_out_consideration = 'Not Excused'
ORDER BY going_out_date DESC 
LIMIT 5;

-- 5. Check recent violations
SELECT v.id, v.student_id, v.violation_date, v.penalty, v.consequence, v.incident_details, 
       vt.violation_name, v.logify_sync_batch_id
FROM violations v
JOIN violation_types vt ON v.violation_type_id = vt.id
WHERE v.logify_sync_batch_id LIKE 'academic_%' OR v.logify_sync_batch_id LIKE 'going_out_%'
ORDER BY v.created_at DESC 
LIMIT 10;
