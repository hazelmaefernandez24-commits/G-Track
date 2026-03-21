@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card" style="max-width:1800px;margin:32px auto;">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Report Details</h4>
                    <div>
                        <a href="{{ route('damage_reports.edit', $report->id) }}" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('damage_reports.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="min-width:1200px;">
                            <tbody>
                                <tr>
                                    <th style="width: 180px;">Photo</th>
                                    <td>
                                        @if($report->photo)
                                            <img src="{{ asset('storage/' . $report->photo) }}" alt="Report Photo" style="max-width:120px;max-height:120px;border-radius:8px;">
                                        @else
                                            <span class="text-muted">No photo</span>
                                        @endif
                                    </td>
                                    <th style="width: 180px;">Report ID</th>
                                    <td>#{{ $report->id }}</td>
                                </tr>
                                <tr>
                                    <th>Title</th>
                                    <td>{{ $report->title }}</td>
                                    <th>Report Date</th>
                                    <td>{{ \Carbon\Carbon::parse($report->report_date)->format('F j, Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Staff In Charge</th>
                                    <td>{{ $report->staff_in_charge }}</td>
                                    <th>Area</th>
                                    <td>{{ $report->area }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($report->status === 'resolved')
                                            <span class="badge bg-success">Resolved</span>
                                        @elseif($report->status === 'active')
                                            <span class="badge bg-warning text-dark">Active</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($report->status) }}</span>
                                        @endif
                                    </td>
                                    <th>Date Created</th>
                                    <td>{{ $report->created_at->format('F j, Y \a\t g:i A') }}</td>
                                </tr>
                                @if($report->updated_at != $report->created_at)
                                <tr>
                                    <th>Last Updated</th>
                                    <td>{{ $report->updated_at->format('F j, Y \a\t g:i A') }}</td>
                                    <th>Date Resolved</th>
                                    <td>
                                        @if($report->date_resolved)
                                            {{ \Carbon\Carbon::parse($report->date_resolved)->format('F j, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Comments</th>
                                    <td colspan="3">{{ $report->comment }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection