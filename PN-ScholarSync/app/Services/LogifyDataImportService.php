<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Carbon\Carbon;

class LogifyDataImportService
{
    protected $logifyConnection;
    protected $testMode;

    public function __construct()
    {
        $this->testMode = env('LOGIFY_TEST_MODE', false);
        
        // We'll configure the Logify database connection dynamically
        $this->setupLogifyConnection();
    }

    /**
     * Setup connection to Logify database
     */
    protected function setupLogifyConnection()
    {
        if ($this->testMode) {
            Log::info('LogifyDataImport: Test mode enabled - using mock data');
            return;
        }

        // Configure Logify database connection
        Config::set('database.connections.logify', [
            'driver' => env('LOGIFY_DB_DRIVER', 'mysql'),
            'host' => env('LOGIFY_DB_HOST', '127.0.0.1'),
            'port' => env('LOGIFY_DB_PORT', '3306'),
            'database' => env('LOGIFY_DB_DATABASE', 'logify'),
            'username' => env('LOGIFY_DB_USERNAME', 'root'),
            'password' => env('LOGIFY_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        $this->logifyConnection = 'logify';
    }

    /**
     * Test connection to Logify database
     */
    public function testConnection()
    {
        if ($this->testMode) {
            Log::info('LogifyDataImport: Test mode - connection test passed');
            return true;
        }

        try {
            DB::connection($this->logifyConnection)->getPdo();
            Log::info('LogifyDataImport: Database connection successful');
            return true;
        } catch (\Exception $e) {
            Log::error('LogifyDataImport: Database connection failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get late students data from Logify database
     * Combines late login/logout from Academic table and late login from Going_out table
     */
    public function getLateStudents($month = null, $year = null, $batch = null)
    {
        if ($this->testMode) {
            return $this->getTestLateStudents($month, $year, $batch);
        }

        try {
            $month = $month ?: now()->format('m');
            $year = $year ?: now()->format('Y');

            // Get late students from Academic table (late login and late logout)
            $academicLateQuery = DB::connection($this->logifyConnection)
                ->table('academics as a')
                ->join('student_details as sd', 'a.student_id', '=', 'sd.student_id')
                ->join('pnph_users as u', 'sd.user_id', '=', 'u.user_id')
                ->select([
                    'a.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    'sd.batch',
                    'sd.group',
                    DB::raw('COUNT(CASE WHEN a.time_in_remark = "late" THEN 1 END) as login_late_count'),
                    DB::raw('COUNT(CASE WHEN a.time_out_remark = "late" THEN 1 END) as logout_late_count')
                ])
                ->whereYear('a.academic_date', $year)
                ->whereMonth('a.academic_date', $month)
                ->where('a.is_deleted', false)
                // Exclude excused or validated late records
                ->where(function ($q) {
                    $q->whereNull('a.educator_consideration')
                      ->orWhere('a.educator_consideration', '<>', 'Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_out_consideration')
                      ->orWhere('a.time_out_consideration', '<>', 'Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_in_absent_validation')
                      ->orWhere('a.time_in_absent_validation', 'Not Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_out_absent_validation')
                      ->orWhere('a.time_out_absent_validation', 'Not Excused');
                });

            if ($batch) {
                $academicLateQuery->where('sd.batch', $batch);
            }

            $academicLateStudents = $academicLateQuery
                ->groupBy(['a.student_id', 'u.user_fname', 'u.user_lname', 'sd.batch', 'sd.group'])
                ->having(DB::raw('login_late_count + logout_late_count'), '>', 0)
                ->get();

            // Get late students from Going_out table (late login only)
            $goingOutLateQuery = DB::connection($this->logifyConnection)
                ->table('going_outs as go')
                ->join('student_details as sd', 'go.student_id', '=', 'sd.student_id')
                ->join('pnph_users as u', 'sd.user_id', '=', 'u.user_id')
                ->select([
                    'go.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    'sd.batch',
                    'sd.group',
                    DB::raw('COUNT(CASE WHEN go.time_in_remark = "late" THEN 1 END) as going_out_late_count')
                ])
                ->whereYear('go.going_out_date', $year)
                ->whereMonth('go.going_out_date', $month)
                ->where('go.is_deleted', false)
                // Import ONLY rows flagged Not Excused
                ->where(function ($q) {
                    $q->where('go.educator_consideration',  'Not Excused')
                      ->orWhere('go.time_out_consideration','Not Excused');
                });

            if ($batch) {
                $goingOutLateQuery->where('sd.batch', $batch);
            }

            $goingOutLateStudents = $goingOutLateQuery
                ->groupBy(['go.student_id', 'u.user_fname', 'u.user_lname', 'sd.batch', 'sd.group'])
                ->having('going_out_late_count', '>', 0)
                ->get();

            // Combine and aggregate the results
            $combinedLateStudents = $this->combineLateStudentData($academicLateStudents, $goingOutLateStudents);

            Log::info('LogifyDataImport: Successfully fetched late students', [
                'academic_late_count' => $academicLateStudents->count(),
                'going_out_late_count' => $goingOutLateStudents->count(),
                'combined_count' => count($combinedLateStudents),
                'month' => $month,
                'year' => $year,
                'batch' => $batch
            ]);

            return [
                'late_students' => $combinedLateStudents,
                'total_count' => count($combinedLateStudents)
            ];

        } catch (\Exception $e) {
            Log::error('LogifyDataImport: Failed to fetch late students', [
                'error' => $e->getMessage(),
                'month' => $month,
                'year' => $year,
                'batch' => $batch
            ]);
            return null;
        }
    }

    /**
     * Get absent students data from Logify database
     * Gets absent records from Academic table only
     */
    public function getAbsentStudents($month = null, $year = null, $batch = null)
    {
        if ($this->testMode) {
            return $this->getTestAbsentStudents($month, $year, $batch);
        }

        try {
            $month = $month ?: now()->format('m');
            $year = $year ?: now()->format('Y');

            // Get absent students from Academic table
            $query = DB::connection($this->logifyConnection)
                ->table('academics as a')
                ->join('student_details as sd', 'a.student_id', '=', 'sd.student_id')
                ->join('pnph_users as u', 'sd.user_id', '=', 'u.user_id')
                ->select([
                    'a.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    'sd.batch',
                    'sd.group',
                    DB::raw("COUNT(CASE WHEN a.time_in_absent_validation = 'Not Excused' OR a.time_out_absent_validation = 'Not Excused' THEN 1 END) as academic_absent_count")
                ])
                ->whereYear('a.academic_date', $year)
                ->whereMonth('a.academic_date', $month)
                ->where('a.is_deleted', false)
                // Exclude excused or validated late records
                ->where(function ($q) {
                    $q->whereNull('a.educator_consideration')
                      ->orWhere('a.educator_consideration', '<>', 'Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_out_consideration')
                      ->orWhere('a.time_out_consideration', '<>', 'Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_in_absent_validation')
                      ->orWhere('a.time_in_absent_validation', 'Not Excused');
                })
                ->where(function ($q) {
                    $q->whereNull('a.time_out_absent_validation')
                      ->orWhere('a.time_out_absent_validation', 'Not Excused');
                });

            if ($batch) {
                $query->where('sd.batch', $batch);
            }

            $absentStudents = $query
                ->groupBy(['a.student_id', 'u.user_fname', 'u.user_lname', 'sd.batch', 'sd.group'])
                ->having('academic_absent_count', '>', 0)
                ->get()
                ->toArray();

            Log::info('LogifyDataImport: Successfully fetched absent students', [
                'count' => count($absentStudents),
                'month' => $month,
                'year' => $year,
                'batch' => $batch
            ]);

            return [
                'absent_students' => $absentStudents,
                'total_count' => count($absentStudents)
            ];

        } catch (\Exception $e) {
            Log::error('LogifyDataImport: Failed to fetch absent students', [
                'error' => $e->getMessage(),
                'month' => $month,
                'year' => $year,
                'batch' => $batch
            ]);
            return null;
        }
    }

    /**
     * Combine late student data from Academic and Going_out tables
     */
    protected function combineLateStudentData($academicLateStudents, $goingOutLateStudents)
    {
        $combined = [];

        // Process academic late students
        foreach ($academicLateStudents as $student) {
            $studentId = $student->student_id;
            $combined[$studentId] = [
                'student_id' => $student->student_id,
                'first_name' => $student->first_name,
                'last_name' => $student->last_name,
                'batch' => $student->batch,
                'group' => $student->group,
                'login_late_count' => $student->login_late_count,
                'logout_late_count' => $student->logout_late_count,
                'going_out_late_count' => 0,
                'total_late_count' => $student->login_late_count + $student->logout_late_count
            ];
        }

        // Add going out late students
        foreach ($goingOutLateStudents as $student) {
            $studentId = $student->student_id;
            if (isset($combined[$studentId])) {
                // Student already exists, add going out late count
                $combined[$studentId]['going_out_late_count'] = $student->going_out_late_count;
                $combined[$studentId]['total_late_count'] += $student->going_out_late_count;
            } else {
                // New student from going out only
                $combined[$studentId] = [
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'batch' => $student->batch,
                    'group' => $student->group,
                    'login_late_count' => 0,
                    'logout_late_count' => 0,
                    'going_out_late_count' => $student->going_out_late_count,
                    'total_late_count' => $student->going_out_late_count
                ];
            }
        }

        return array_values($combined);
    }

    /**
     * Check if there are recent updates in Logify database
     */
    public function hasRecentUpdates($since = null)
    {
        if ($this->testMode) {
            return true; // Always return true in test mode
        }

        try {
            $since = $since ?: Carbon::now()->subHour()->toDateTimeString();

            // Check for recent updates in Academic table
            $academicUpdates = DB::connection($this->logifyConnection)
                ->table('academics')
                ->where('updated_at', '>=', $since)
                ->where('is_deleted', false)
                ->count();

            // Check for recent updates in Going_out table
            $goingOutUpdates = DB::connection($this->logifyConnection)
                ->table('going_outs')
                ->where('updated_at', '>=', $since)
                ->where('is_deleted', false)
                ->count();

            return ($academicUpdates + $goingOutUpdates) > 0;

        } catch (\Exception $e) {
            Log::error('LogifyDataImport: Failed to check for recent updates', [
                'error' => $e->getMessage(),
                'since' => $since
            ]);
            return false;
        }
    }

    /**
     * Test mode methods - simulate database responses for testing
     */
    protected function getTestLateStudents($month = null, $year = null, $batch = null)
    {
        Log::info('LogifyDataImport: Test mode - returning mock late students data');
        
        return [
            'late_students' => [
                [
                    'student_id' => '2025010001C1',
                    'first_name' => 'John Paul',
                    'last_name' => 'Casaldan',
                    'batch' => '2025',
                    'group' => 'PN1',
                    'total_late_count' => 3
                ],
                [
                    'student_id' => '2025010003C1',
                    'first_name' => 'Mark Kevin',
                    'last_name' => 'Chavez',
                    'batch' => '2025',
                    'group' => 'PN2',
                    'total_late_count' => 2
                ]
            ],
            'total_count' => 2
        ];
    }

    protected function getTestAbsentStudents($month = null, $year = null, $batch = null)
    {
        Log::info('LogifyDataImport: Test mode - returning mock absent students data');
        
        return [
            'absent_students' => [
                [
                    'student_id' => '2025010003C1',
                    'first_name' => 'Mark Kevin',
                    'last_name' => 'Chavez',
                    'batch' => '2025',
                    'group' => 'PN2',
                    'academic_absent_count' => 1
                ]
            ],
            'total_count' => 1
        ];
    }
}
