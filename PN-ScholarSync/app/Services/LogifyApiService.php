<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class LogifyApiService
{
    protected $baseUrl;
    protected $timeout;
    protected $retryAttempts;
    protected $retryDelay;
    protected $testMode;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('LOGIFY_API_BASE_URL', 'http://localhost:8000'), '/');
        $this->timeout = env('LOGIFY_API_TIMEOUT', 30);
        $this->retryAttempts = env('LOGIFY_API_RETRY_ATTEMPTS', 3);
        $this->retryDelay = env('LOGIFY_API_RETRY_DELAY', 1000); // milliseconds
        $this->testMode = env('LOGIFY_TEST_MODE', false);
    }

    /**
     * Check if API integration is enabled
     */
    public function isApiEnabled()
    {
        return env('LOGIFY_API_ENABLED', false);
    }

    /**
     * Check if there are any recent updates (late or absent students)
     * This is used to determine if we should fetch data
     */
    public function hasRecentUpdates($since = null)
    {
        if ($this->testMode) {
            return $this->getTestRecentUpdates($since);
        }

        try {
            $since = $since ?: $this->getLastSyncTimestamp();
            
            $response = $this->makeRequest('GET', '/api/scholar-sync/recent-updates', [
                'since' => $since,
                'limit' => 1 // We only need to know if there are any updates
            ]);

            if ($response && $response['success']) {
                return count($response['data']['updates'] ?? []) > 0;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('LogifyApiService: Failed to check for recent updates', [
                'error' => $e->getMessage(),
                'since' => $since
            ]);
            return false;
        }
    }

    /**
     * Get late students data from Logify
     */
    public function getLateStudents($month = null, $year = null, $batch = null, $since = null)
    {
        if ($this->testMode) {
            return $this->getTestLateStudents($month, $year, $batch, $since);
        }

        try {
            $params = array_filter([
                'month' => $month ?: now()->format('m'),
                'year' => $year ?: now()->format('Y'),
                'batch' => $batch,
                'since' => $since
            ]);

            $response = $this->makeRequest('GET', '/api/scholar-sync/late-students', $params);

            if ($response && $response['success']) {
                Log::info('LogifyApiService: Successfully fetched late students', [
                    'count' => $response['data']['total_count'] ?? 0,
                    'params' => $params
                ]);
                return $response['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LogifyApiService: Failed to fetch late students', [
                'error' => $e->getMessage(),
                'params' => $params ?? []
            ]);
            return null;
        }
    }

    /**
     * Get absent students data from Logify
     */
    public function getAbsentStudents($month = null, $year = null, $batch = null, $since = null)
    {
        if ($this->testMode) {
            return $this->getTestAbsentStudents($month, $year, $batch, $since);
        }

        try {
            $params = array_filter([
                'month' => $month ?: now()->format('m'),
                'year' => $year ?: now()->format('Y'),
                'batch' => $batch,
                'since' => $since
            ]);

            $response = $this->makeRequest('GET', '/api/scholar-sync/absent-students', $params);

            if ($response && $response['success']) {
                Log::info('LogifyApiService: Successfully fetched absent students', [
                    'count' => $response['data']['total_count'] ?? 0,
                    'params' => $params
                ]);
                return $response['data'];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('LogifyApiService: Failed to fetch absent students', [
                'error' => $e->getMessage(),
                'params' => $params ?? []
            ]);
            return null;
        }
    }

    /**
     * Test the connection to Logify API
     */
    public function testConnection()
    {
        // If API is disabled, always return false
        if (!$this->isApiEnabled()) {
            Log::info('LogifyApiService: API integration is disabled - use database import instead');
            return false;
        }

        if ($this->testMode) {
            Log::info('LogifyApiService: Test mode - connection test passed');
            return true;
        }

        try {
            $response = $this->makeRequest('GET', '/api/scholar-sync/recent-updates', ['limit' => 1]);
            return $response !== null;
        } catch (\Exception $e) {
            Log::error('LogifyApiService: Connection test failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Make HTTP request to Logify API with retry logic
     */
    protected function makeRequest($method, $endpoint, $params = [])
    {
        $url = $this->baseUrl . $endpoint;
        $attempt = 0;

        while ($attempt < $this->retryAttempts) {
            try {
                $response = Http::timeout($this->timeout)->$method($url, $params);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('LogifyApiService: API request failed', [
                    'url' => $url,
                    'method' => $method,
                    'status' => $response->status(),
                    'attempt' => $attempt + 1
                ]);

                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay / 1000);
                }

            } catch (\Exception $e) {
                Log::error('LogifyApiService: HTTP request exception', [
                    'url' => $url,
                    'method' => $method,
                    'error' => $e->getMessage(),
                    'attempt' => $attempt + 1
                ]);

                $attempt++;
                
                if ($attempt < $this->retryAttempts) {
                    sleep($this->retryDelay / 1000);
                }
            }
        }

        throw new \Exception("Failed to make request to Logify API after {$this->retryAttempts} attempts");
    }

    /**
     * Get the last sync timestamp from cache
     */
    protected function getLastSyncTimestamp()
    {
        return Cache::get('logify_last_sync', Carbon::now()->subHour()->toISOString());
    }

    /**
     * Update the last sync timestamp
     */
    public function updateLastSyncTimestamp($timestamp = null)
    {
        $timestamp = $timestamp ?: Carbon::now()->toISOString();
        Cache::put('logify_last_sync', $timestamp, now()->addDays(7));
        
        Log::info('LogifyApiService: Updated last sync timestamp', [
            'timestamp' => $timestamp
        ]);
    }

    /**
     * Test mode methods - simulate API responses for testing
     */
    protected function getTestRecentUpdates($since = null)
    {
        Log::info('LogifyApiService: Test mode - simulating recent updates');
        return true;
    }

    protected function getTestLateStudents($month = null, $year = null, $batch = null, $since = null)
    {
        Log::info('LogifyApiService: Test mode - returning mock late students data');
        
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

    protected function getTestAbsentStudents($month = null, $year = null, $batch = null, $since = null)
    {
        Log::info('LogifyApiService: Test mode - returning mock absent students data');
        
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
