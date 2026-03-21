<?php

namespace App\Traits;

use App\Models\Violation;
use Illuminate\Support\Facades\Log;

trait ViolationCountingTrait
{
    /**
     * Count violations by batch filter
     *
     * @param string $batch
     * @param string $status
     * @return int
     */
    protected function countViolationsByBatch($batch = 'all', $status = null)
    {
        try {
            $query = Violation::query();

            // Apply status filter if provided
            if ($status) {
                $query->where('status', $status);
            } else {
                // Exclude approved appeals from violation counts
                $query->where('status', '!=', 'appeal_approved');
            }

            // Apply batch filter if not 'all'
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 2025% for Class 2025)
                $query->where('student_id', 'like', $batch . '%');
            }

            return $query->count();
        } catch (\Exception $e) {
            Log::error('Error in countViolationsByBatch: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count active violations by batch
     *
     * @param string $batch
     * @return int
     */
    protected function countActiveViolationsByBatch($batch = 'all')
    {
        return $this->countViolationsByBatch($batch, 'active');
    }

    /**
     * Count resolved violations by batch
     *
     * @param string $batch
     * @return int
     */
    protected function countResolvedViolationsByBatch($batch = 'all')
    {
        return $this->countViolationsByBatch($batch, 'resolved');
    }

    /**
     * Count total violations by batch (all statuses)
     *
     * @param string $batch
     * @return int
     */
    protected function countTotalViolationsByBatch($batch = 'all')
    {
        return $this->countViolationsByBatch($batch);
    }

    /**
     * Get violation count response in standard format
     *
     * @param string $batch
     * @param string $status
     * @return array
     */
    protected function getViolationCountResponse($batch, $status = null)
    {
        try {
            $count = $this->countViolationsByBatch($batch, $status);

            return [
                'success' => true,
                'count' => $count,
                'batch' => $batch
            ];
        } catch (\Exception $e) {
            Log::error('Error in getViolationCountResponse: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fetching violations count: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }
}
