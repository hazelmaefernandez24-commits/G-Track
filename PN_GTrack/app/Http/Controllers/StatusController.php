<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index()
{
    $students = Student::with('locations')->get();

    foreach ($students as $student) {
        $lastLocation = $student->locations()->latest()->first();

        if ($lastLocation) {
            $diff = now()->diffInMinutes($lastLocation->recorded_at);

            $student->status = $diff >= 10 ? 'offline' : 'online';
            $student->last_seen = $lastLocation->recorded_at;
        }
    }

    return response()->json($students);
}
}
