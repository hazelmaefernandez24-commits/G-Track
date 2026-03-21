<?php

namespace App\Http\Controllers;

use App\Services\ErrorPreventionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    /**
     * Safe API response wrapper
     */
    protected function safeApiResponse($callback, $errorMessage = 'Operation failed')
    {
        try {
            $result = $callback();
            
            if (is_array($result) && isset($result['success']) && $result['success'] === false) {
                return response()->json($result, 400);
            }
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
            
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database Query Error', [
                'error' => $e->getMessage(),
                'sql' => $e->getSql(),
                'bindings' => $e->getBindings(),
                'user_id' => auth()->id(),
                'route' => request()->route()->getName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Database error occurred. Please try again.',
                'error_code' => 'DB_ERROR'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('General Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'route' => request()->route()->getName()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'error_code' => 'GENERAL_ERROR'
            ], 500);
        }
    }

    /**
     * Safe method call with error handling
     */
    protected function safeMethodCall($object, $method, $parameters = [], $default = null)
    {
        return ErrorPreventionService::safeMethodCall($object, $method, $parameters, $default);
    }

    /**
     * Safe table query with error handling
     */
    protected function safeTableQuery($table, $callback, $default = [])
    {
        return ErrorPreventionService::safeTableQuery($table, $callback, $default);
    }

    /**
     * Safe column query with error handling
     */
    protected function safeColumnQuery($table, $column, $callback, $default = [])
    {
        return ErrorPreventionService::safeColumnQuery($table, $column, $callback, $default);
    }

    /**
     * Get meal status safely
     */
    protected function getMealStatus($meal, $date = null)
    {
        return ErrorPreventionService::getMealStatus($meal, $date);
    }

    /**
     * Validate required parameters
     */
    protected function validateRequiredParams(Request $request, array $required)
    {
        $missing = [];
        foreach ($required as $param) {
            if (!$request->has($param) || $request->get($param) === null) {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            return [
                'success' => false,
                'message' => 'Missing required parameters: ' . implode(', ', $missing),
                'missing_params' => $missing
            ];
        }

        return ['success' => true];
    }

    /**
     * Safe pagination with error handling
     */
    protected function safePaginate($query, $perPage = 15)
    {
        try {
            return $query->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Pagination Error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            // Return empty collection if pagination fails
            return collect([]);
        }
    }

    /**
     * Safe model creation with validation
     */
    protected function safeCreate($model, array $data)
    {
        try {
            if (!class_exists($model)) {
                throw new \Exception("Model {$model} does not exist");
            }

            return $model::create($data);
            
        } catch (\Exception $e) {
            Log::error('Model Creation Error', [
                'model' => $model,
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }

    /**
     * Safe model update with validation
     */
    protected function safeUpdate($model, array $data)
    {
        try {
            if (!is_object($model)) {
                throw new \Exception("Invalid model instance provided");
            }

            return $model->update($data);
            
        } catch (\Exception $e) {
            Log::error('Model Update Error', [
                'model' => get_class($model),
                'model_id' => $model->id ?? 'unknown',
                'data' => $data,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }

    /**
     * Safe file upload handling
     */
    protected function safeFileUpload(Request $request, $fieldName, $path = 'uploads')
    {
        try {
            if (!$request->hasFile($fieldName)) {
                return null;
            }

            $file = $request->file($fieldName);
            
            if (!$file->isValid()) {
                throw new \Exception('Invalid file upload');
            }

            return $file->store($path, 'public');
            
        } catch (\Exception $e) {
            Log::error('File Upload Error', [
                'field' => $fieldName,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }

    /**
     * Log user action for debugging
     */
    protected function logUserAction($action, $data = [])
    {
        Log::info('User Action', [
            'action' => $action,
            'user_id' => auth()->id(),
            'user_role' => auth()->user()->role ?? 'unknown',
            'route' => request()->route()->getName(),
            'ip' => request()->ip(),
            'data' => $data,
            'timestamp' => now()
        ]);
    }

    /**
     * Check if user has required role
     */
    protected function requireRole($role)
    {
        if (!auth()->check()) {
            abort(401, 'Authentication required');
        }

        if (auth()->user()->role !== $role) {
            abort(403, "Access denied. Required role: {$role}");
        }
    }

    /**
     * Get current week cycle safely
     */
    protected function getCurrentWeekCycle()
    {
        try {
            if (class_exists('\App\Services\WeekCycleService')) {
                $weekInfo = \App\Services\WeekCycleService::getWeekInfo();
                return $weekInfo['week_cycle'];
            }
            
            // Fallback calculation with week 4 cap
            $weekOfMonth = min(now()->weekOfMonth, 4);
            return ($weekOfMonth % 2 === 1) ? 1 : 2;
            
        } catch (\Exception $e) {
            Log::error('Week Cycle Calculation Error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return 1; // Default to week 1
        }
    }
}
