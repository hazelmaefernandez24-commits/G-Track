<?php

namespace App\Http\Controllers;

use App\Models\OffenseCategory;
use App\Models\ViolationType;
use Illuminate\Http\Request;

class EducatorManualController extends Controller
{

    public function index()
    {
        $categories = OffenseCategory::with(['violationTypes' => function($query) {
            $query->with('severityRelation')
                  ->orderByRaw("FIELD(severity_id, 1, 2, 3, 4)")
                  ->orderBy('violation_name');
        }])->get();

        // Get penalty options for the severity configuration table
        $penaltyOptions = \App\Models\PenaltyConfiguration::getForDropdown();

        return view('educator.educator-manual', compact('categories', 'penaltyOptions'));
    }

    
}