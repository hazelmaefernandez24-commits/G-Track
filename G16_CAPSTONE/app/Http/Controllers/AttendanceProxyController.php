<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceProxyController extends Controller
{
    /**
     * Proxy attendance data from the Login DB tables (academics, going_outs, intern_log, going_home)
     */
    public function studentAttendance(Request $request, $studentId)
    {
        try {
            // Use the 'login' DB connection which should point to the Login/Logify database
            $conn = DB::connection('login');

            $today = date('Y-m-d');

            $result = [
                'status' => 'unknown',
                'time_in' => null,
                'time_in_remark' => null,
                'time_out' => null,
                'time_out_remark' => null,
                'task_eligible' => true,
            ];

            // Helper: try each table in order and merge results; prefer records for today
            $tables = ['academics', 'going_outs', 'intern_log', 'going_home'];

            foreach ($tables as $table) {
                try {
                    if (!$conn->getSchemaBuilder()->hasTable($table)) continue;

                    // Determine date/field names variations per table
                    $dateField = in_array($table, ['going_home']) ? 'date_time_out' : (in_array($table, ['intern_log']) ? 'date' : (in_array($table, ['going_outs']) ? 'going_out_date' : 'academic_date'));

                    $row = $conn->table($table)
                        ->where(function($q) use ($studentId) {
                            $q->where('student_id', $studentId)->orWhere('student_id_code', $studentId);
                        })
                        ->where(function($q) use ($dateField, $today) {
                            $q->where($dateField, $today)->orWhereNull($dateField);
                        })
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($row) {
                        // Map common fields
                        $ti = $row->time_in ?? ($row->date_time_in ?? null) ?? null;
                        $to = $row->time_out ?? null;
                        $ti_rem = $row->time_in_remark ?? $row->time_in_remarks ?? null;
                        $to_rem = $row->time_out_remark ?? $row->time_out_remarks ?? $row->time_out_remark ?? null;

                        if ($ti) {
                            $result['time_in'] = $ti;
                            $result['time_in_remark'] = $ti_rem;
                        }
                        if ($to) {
                            $result['time_out'] = $to;
                            $result['time_out_remark'] = $to_rem;
                        }

                        // If both present mark present
                        if ($result['time_in'] && $result['time_out']) {
                            $result['status'] = 'present';
                            $result['task_eligible'] = true;
                            break; // no need to check older tables
                        }

                        // If time_out present but no time_in yet -> absent, not eligible
                        if ($result['time_out'] && !$result['time_in']) {
                            $result['status'] = 'absent';
                            $result['task_eligible'] = false;
                            // continue checking other tables in case a matching time_in exists elsewhere
                        }
                    }
                } catch (\Throwable $e) {
                    // ignore table-specific errors and continue
                    continue;
                }
            }

            // If no records found across tables, treat as present in center
            if (!$result['time_in'] && !$result['time_out']) {
                $result['status'] = 'present';
                $result['task_eligible'] = true;
            }

            return response()->json($result);

        } catch (\Throwable $e) {
            \Log::error('AttendanceProxy error: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch attendance'], 500);
        }
    }
}
