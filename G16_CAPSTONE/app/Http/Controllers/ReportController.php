<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use Illuminate\Support\Facades\Log;
use App\Exports\ReportsExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Report::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('comment', 'like', "%{$search}%")
                  ->orWhere('staff_in_charge', 'like', "%{$search}%")
                  ->orWhere('area', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('report_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('report_date', '<=', $request->date_to);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['created_at', 'report_date', 'title', 'status', 'staff_in_charge', 'area'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $reports = $query->paginate(15)->withQueryString();

        return view('damage_reports.index', compact('reports'));
    }

    public function create()
    {
        // Show the create report form in damage_reports views
        return view('damage_reports.report');
    }

    public function store(Request $request)
    {
        try {
            Log::info('Starting report creation process', [
                'request_data' => $request->all(),
                'has_photo' => $request->hasFile('photo'),
                'photo_info' => $request->hasFile('photo') ? [
                    'name' => $request->file('photo')->getClientOriginalName(),
                    'size' => $request->file('photo')->getSize(),
                    'mime' => $request->file('photo')->getMimeType()
                ] : null
            ]);

            $request->validate([
                'photo' => 'nullable|image|max:2048', // max 2MB; optional
                'report_date' => 'required|date',
                'title' => 'required|string|max:255',
                'comment' => 'required|string',
                // allow pending, active, resolved statuses
                'status' => 'required|in:pending,active,resolved',
                'date_resolved' => 'nullable|date|required_if:status,resolved',
                'staff_in_charge' => 'required|string|max:255',
                'area' => 'required|string|max:255'
            ]);

            Log::info('Validation passed', [
                'report_date' => $request->report_date,
                'title' => $request->title,
                'has_photo' => $request->hasFile('photo')
            ]);

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoPath = $photo->store('reports', 'public');
                Log::info('Photo uploaded successfully', ['path' => $photoPath]);
            }

            Log::info('Creating report with data', [
                'photo_path' => $photoPath,
                'report_date' => $request->report_date,
                'title' => $request->title,
                'comment' => $request->comment,
            ]);
            // Create report
            $report = Report::create([
                'photo_path' => $photoPath,
                'report_date' => $request->report_date,
                'title' => $request->title,
                'comment' => $request->comment,
                'status' => $request->status,
                'date_resolved' => $request->status === 'resolved' ? $request->date_resolved : null,
                'staff_in_charge' => $request->staff_in_charge,
                'area' => $request->area,
            ]);

            Log::info('Report created successfully', ['report_id' => $report->id]);

            // Redirect to damage reports index (UI pages under damage_reports)
            return redirect()->route('damage_reports.index')
                ->with('success', 'Report submitted successfully!')
                ->withHeaders([
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache'
                ]);

        } catch (\Exception $e) {
            Log::error('Failed to create report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Failed to save report: ' . $e->getMessage()]);
        }
    }

    public function show(Report $report)
    {
        return view('damage_reports.show', compact('report'));
    }

    public function edit(Report $report)
    {
        return view('damage_reports.edit', compact('report'));
    }

    public function update(Request $request, Report $report)
    {
        try {
            $request->validate([
                'photo' => 'nullable|image|max:2048',
                'report_date' => 'required|date',
                'title' => 'required|string|max:255',
                'comment' => 'required|string',
                // Allow 'pending' during updates too (controller accepts it on create)
                'status' => 'required|in:pending,active,resolved',
                'date_resolved' => 'nullable|date|required_if:status,resolved',
                'staff_in_charge' => 'required|string|max:255',
                'area' => 'required|string|max:255'
            ]);

            // Handle photo upload if new photo is provided
            if ($request->hasFile('photo')) {
                // Delete old photo
                if ($report->photo_path) {
                    Storage::disk('public')->delete($report->photo_path);
                }

                $photo = $request->file('photo');
                $photoPath = $photo->store('reports', 'public');
                $report->photo_path = $photoPath;
            }

            // Update report
            $updateData = [
                'report_date' => $request->report_date,
                'title' => $request->title,
                'comment' => $request->comment,
                'status' => $request->status,
                'date_resolved' => $request->status === 'resolved' ? $request->date_resolved : null,
                'staff_in_charge' => $request->staff_in_charge,
                'area' => $request->area,
            ];

            // Include photo_path if it was updated
            if (isset($photoPath)) {
                $updateData['photo_path'] = $photoPath;
            }

            $report->update($updateData);

            return redirect()->route('damage_reports.index')
                ->with('success', 'Report updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()
                ->withErrors(['error' => 'Failed to update report: ' . $e->getMessage()]);
        }
    }

    public function destroy(Report $report)
    {
        try {
            // Delete photo from storage
            if ($report->photo_path) {
                Storage::disk('public')->delete($report->photo_path);
            }

            // Delete report
            $report->delete();

            return redirect()->route('damage_reports.index')
                ->with('success', 'Report deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Failed to delete report: ' . $e->getMessage()]);
        }
    }
}