<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Visitor;
use Illuminate\Support\Facades\Log;
use App\Models\NotificationView;
use App\Models\NotificationHistory;
use Illuminate\Support\Facades\Auth;

class VisitorLogController extends Controller
{
    public function create()
    {
        return view('visitor.visitor-log');
    }

    public function store(Request $request)
    {
        $request->validate([
            'valid_id' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'id_number' => 'required|string|max:255',
            'relationship' => 'required|string|max:100',
            'purpose' => 'required|string|max:255',
            'other_id_type' => 'required_if:valid_id,Other|nullable|string|max:255',
        ]);

        $validId = $request->valid_id === 'Other' ? $request->other_id_type : $request->valid_id;

        $maxPassNumber = 10;

        $usedPasses = Visitor::whereDate('visit_date', now()->format('Y-m-d'))
            ->whereNull('time_out')
            ->pluck('visitor_pass')
            ->toArray();

        $visitorPass = null;
        for ($i = 1; $i <= $maxPassNumber; $i++) {
            if (!in_array($i, $usedPasses)) {
                $visitorPass = $i;
                break;
            }
        }

        if ($visitorPass === null) {
            return back()->withErrors([
                'visitor_pass' => 'All visitor passes are currently in use.',
            ]);
        }

        Visitor::create([
            'guard_id' => session('user.id'),
            'pass' => $visitorPass,
            'name' => $request->visitor_name,
            'visitor_id' => $request->id,
            'valid_id' => $validId,
            'id_number' => $request->id_number,
            'relationship' => $request->relationship,
            'purpose' => $request->purpose,
            'date' => date('Y-m-d'),
            'time_in' => date('H:i:s'),
            'time_out' => null,
        ]);

        NotificationHistory::createNotification(
            $request->visitor_name,
            null,
            'time_in',
            'visitor',
            false,
            'On Time'
        );

        return redirect()->route('visitor.dashboard.show')->with('success', 'Visitor log created successfully!');
    }

    public function logOut(Request $request, $id)
    {
        $visitor = Visitor::findOrFail($id);
        $visitor->time_out = now()->setTimezone('Asia/Manila')->format('H:i:s');
        $visitor->save();

        NotificationHistory::createNotification(
            $visitor->visitor_name,
            null,
            'time_out',
            'visitor',
            false,
            'On Time'
        );

        return redirect()->back()->with('success', 'Time out logged successfully!');
    }

    /**
     * Monitor past visitor logs with filtering capabilities.
     */
    public function pastLogs(Request $request)
    {
        $query = Visitor::query()
            ->orderBy('visit_date', 'desc')
            ->orderBy('time_in', 'desc');

        if ($request->has('month') && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year = date('Y', strtotime($request->month));
            $query->whereMonth('visit_date', $month)
                ->whereYear('visit_date', $year);
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('visit_date', $request->date);
        }

        $visitors = $query->paginate(20)->appends($request->query());

        return view('user-educator.visitormonitor', [
            'visitors' => $visitors,
            'isPastLogs' => true,
            'selectedMonth' => $request->month ?? '',
            'selectedDate' => $request->date ?? '',
        ]);
    }

    /**
     * Accept a visitor request.
     */
    public function accept(Request $request, $id)
    {
        try {
            $visitor = Visitor::findOrFail($id);
            $visitor->status = 'accepted';
            $visitor->save();

            return response()->json([
                'success' => true,
                'message' => 'Visitor request accepted successfully.'
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept visitor request.'
            ], 500);
        }
    }

    /**
     * Reject a visitor request.
     */
    public function reject(Request $request, $id)
    {
        try {
            $visitor = Visitor::findOrFail($id);
            $visitor->status = 'rejected';
            $visitor->save();

            return response()->json([
                'success' => true,
                'message' => 'Visitor request rejected successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject visitor request.'
            ], 500);
        }
    }

    /**
     * Update visitor consideration.
     */
    public function updateConsideration(Request $request, $id)
    {
        try {
            $request->validate([
                'consideration' => 'required|in:Approved,Denied',
                'reason' => 'nullable|string|max:255'
            ]);

            $visitor = Visitor::findOrFail($id);
            $visitor->consideration = $request->consideration;
            $visitor->consideration_reason = $request->reason;
            $visitor->save();

            return response()->json([
                'success' => true,
                'message' => 'Visitor consideration updated successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update visitor consideration.'
            ], 500);
        }
    }

    /**
     * Approve a visitor manual entry
     */
    public function approveManualEntry(Request $request, $id)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:500'
        ]);

        try {
            $visitor = Visitor::findOrFail($id);

            if (!$visitor->is_manual_entry || $visitor->approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This visitor entry is not pending manual entry approval.'
                ], 400);
            }

            $educatorName = Auth::user()->user_fname . ' ' . Auth::user()->user_lname;

            $visitor->update([
                'approval_status' => 'approved',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
                'updated_by' => $educatorName,
                'updated_at' => now()
            ]);

            if ($visitor->time_in) {
                NotificationHistory::createNotification(
                    $visitor->visitor_name,
                    null,
                    'time_in',
                    'visitor',
                    false,
                    'On Time'
                );
            }

            if ($visitor->time_out) {
                NotificationHistory::createNotification(
                    $visitor->visitor_name,
                    null,
                    'time_out',
                    'visitor',
                    false,
                    'On Time'
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Visitor manual entry approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve visitor manual entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a visitor manual entry
     */
    public function rejectManualEntry(Request $request, $id)
    {
        $request->validate([
            'approval_notes' => 'required|string|max:500'
        ]);

        try {
            $visitor = Visitor::findOrFail($id);

            if (!$visitor->is_manual_entry || $visitor->approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This visitor entry is not pending manual entry approval.'
                ], 400);
            }

            $educatorName = session('user.user_fname') . ' ' . session('user.user_lname');

            $visitor->update([
                'approval_status' => 'rejected',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->approval_notes,
                'updated_by' => $educatorName,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor manual entry rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject visitor manual entry: ' . $e->getMessage()
            ], 500);
        }
    }
}
