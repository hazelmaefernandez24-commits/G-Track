<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::active()->get();
        
        // Format events for display
        $calendarEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'description' => $event->description,
                'category' => $event->category,
                'backgroundColor' => $this->getCategoryColor($event->category),
                'borderColor' => $this->getCategoryColor($event->category),
            ];
        });

        return view('student.calendar.index', [
            'title' => 'Academic Calendar',
            'events' => $calendarEvents
        ]);
    }

    public function getEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $events = CalendarEvent::active()
            ->whereBetween('start_date', [$start, $end])
            ->orWhereBetween('end_date', [$start, $end])
            ->get();

        $calendarEvents = $events->map(function ($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->start_date,
                'end' => $event->end_date,
                'description' => $event->description,
                'category' => $event->category,
                'backgroundColor' => $this->getCategoryColor($event->category),
                'borderColor' => $this->getCategoryColor($event->category),
            ];
        });

        return response()->json($calendarEvents);
    }

    private function getCategoryColor($category)
    {
        $colors = [
            'school_activity' => '#3498db',  // Blue
            'holiday' => '#e74c3c',         // Red
            'examination' => '#f39c12',     // Orange
            'deadline' => '#e67e22',        // Dark Orange
            'vacation' => '#27ae60',        // Green
            'special' => '#9b59b6',         // Purple
        ];

        return $colors[$category] ?? '#95a5a6'; // Default gray
    }
}
