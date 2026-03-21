<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::all();
        
        // Format events for FullCalendar
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

        return view('training.calendar.index', [
            'title' => 'Academic Calendar',
            'events' => $calendarEvents
        ]);
    }

    public function manage()
    {
        $events = CalendarEvent::orderBy('start_date', 'desc')->paginate(15);
        
        return view('training.calendar.manage', [
            'title' => 'Manage Calendar Events',
            'events' => $events
        ]);
    }

    public function create()
    {
        return view('training.calendar.create', [
            'title' => 'Add New Event'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'required|in:school_activity,holiday,examination,deadline,vacation,special',
            'semester' => 'nullable|in:first,second,summer',
            'academic_year' => 'required|string'
        ]);

        CalendarEvent::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'category' => $request->category,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year,
            'is_active' => true
        ]);

        return redirect()->route('training.calendar.manage')
                        ->with('success', 'Event created successfully!');
    }

    public function edit(CalendarEvent $calendarEvent)
    {
        return view('training.calendar.edit', [
            'title' => 'Edit Event',
            'event' => $calendarEvent
        ]);
    }

    public function update(Request $request, CalendarEvent $calendarEvent)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category' => 'required|in:school_activity,holiday,examination,deadline,vacation,special',
            'semester' => 'nullable|in:first,second,summer',
            'academic_year' => 'required|string'
        ]);

        $calendarEvent->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'category' => $request->category,
            'semester' => $request->semester,
            'academic_year' => $request->academic_year
        ]);

        return redirect()->route('training.calendar.manage')
                        ->with('success', 'Event updated successfully!');
    }

    public function destroy(CalendarEvent $calendarEvent)
    {
        $calendarEvent->delete();
        
        return redirect()->route('training.calendar.manage')
                        ->with('success', 'Event deleted successfully!');
    }

    public function getEvents(Request $request)
    {
        $start = $request->get('start');
        $end = $request->get('end');

        $events = CalendarEvent::whereBetween('start_date', [$start, $end])
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
