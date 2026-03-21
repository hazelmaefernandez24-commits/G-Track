<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RotationScheduleController extends Controller
{
    /**
     * NOTE: RotationSchedule model/controller were migrated to the G16_CAPSTONE app.
     * The migration remains in the Login app (database/migrations/...).
     * This lightweight endpoint returns guidance so API consumers are redirected to use
     * the G16_CAPSTONE app's endpoints.
     */
    public function __call($method, $args)
    {
        return response()->json([
            'success' => false,
            'message' => 'RotationSchedule controller moved to the G16_CAPSTONE application. Use the G16_CAPSTONE endpoints (rotation schedules) instead. Migration remains in Login/migrations.',
        ], 410);
    }

    /**
     * Return guidance pointing consumers to the G16_CAPSTONE app.
     */
    public function index(Request $request)
    {
        return $this->movedResponse('/api/rotation-schedules', $request);
    }

    public function show(Request $request, $id)
    {
        return $this->movedResponse("/api/rotation-schedules/{$id}", $request);
    }

    public function store(Request $request)
    {
        return $this->movedResponse('/api/rotation-schedules', $request);
    }

    public function update(Request $request, $id)
    {
        return $this->movedResponse("/api/rotation-schedules/{$id}", $request);
    }

    public function destroy(Request $request, $id)
    {
        return $this->movedResponse("/api/rotation-schedules/{$id}", $request);
    }

    /**
     * Build a consistent 410 response with a suggested G16_CAPSTONE redirect URL.
     */
    protected function movedResponse(string $path, Request $request)
    {
        $base = env('G16_CAPSTONE_URL') ?: env('SYSTEM_3_URL') ?: null;
        $redirect = $base ? rtrim($base, '/') . $path : null;

        // preserve token query param if supplied
        $token = $request->query('token');
        if ($redirect && $token) {
            $sep = strpos($redirect, '?') === false ? '?' : '&';
            $redirect .= $sep . 'token=' . urlencode($token);
        }

        return response()->json([
            'success' => false,
            'message' => 'RotationSchedule endpoints have moved to the G16_CAPSTONE application. Use the G16_CAPSTONE rotation schedules endpoints.',
            'redirect_url' => $redirect,
        ], 410);
    }
}