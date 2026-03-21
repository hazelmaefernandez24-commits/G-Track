<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitorLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class VisitorLogController extends Controller
{
    public function create()
    {
        return view('visitor.visitor-log');
    }

    public function store(Request $request)
    {
        $request->validate([
            'guard_id' => [
                'required',
                'string',
                Rule::exists('pnph_users', 'user_id')->where(function ($query) {
                    $query->where('user_role', 'guard');
                }),
            ],
            'valid_id' => 'required|string|max:255',
            'visitor_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'id_number' => 'required|string|max:255',
            'relationship' => 'max:100',
            'purpose' => 'required|string|max:255',
        ]);

        $maxPassNumber = 10;

        $usedPasses = VisitorLog::getUsedPass();

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

        $isAlreadyIn = VisitorLog::getVisitorID($request['valid_id'], $request['id_number']);
        if($isAlreadyIn){
            return back()->withErrors([
                'error' => 'You have already logged in this ' . Carbon::parse($isAlreadyIn->time_in)->format('h:i A') . '  today.'
            ]);
        }

        $data = [
            'guard_id' => $request->guard_id,
            'visitor_pass' => $visitorPass,
            'visitor_name' => $request->visitor_name,
            'valid_id' => $request->valid_id,
            'id_number' => $request->id_number,
            'relationship' => $request->relationship,
            'purpose' => $request->purpose,
            'visit_date' => date('Y-m-d'),
            'time_in' => date('H:i:s'),
            'created_by' => $request->visitor_name,
            'created_at' => now(),
            'updated_by' => $request->visitor_name,
            'updated_at' => now(),
        ];

        VisitorLog::saveData($data);

        $visitors = VisitorLog::all();
        return redirect()->route('visitor.dashboard.show', compact('visitors'))->with('success', 'Visitor log created successfully!');
    }

    public function logOut(Request $request, $id)
    {
        $data = [
            'time_out'   => now()->format('H:i:s'),
            'updated_at' => now(),
        ];
        $visitor = VisitorLog::findOrFail($id)->update($data);
        // $visitor->save();
        return redirect()->back()->with('success', 'Time out logged successfully!');
    }
}
