@extends('layouts.apps')

@section('content')
@php
  $tasks = [
    // KITCHEN
    'Assigned members woke up on time and completed their tasks as scheduled.',
    'The students assigned to cook the rice completed the task properly.',
    'The students assigned to cook the viand completed the task properly.',
    'The students assigned to assist the cook carried out their duties diligently.',
    'Ingredients were prepared ahead of time.',
    'The kitchen was properly cleaned after cooking.',
    'The food was transferred from the kitchen to the center.',
    'Proper inventory of stocks was maintained and deliveries were handled appropriately.',
    'Water and food supplies were regularly monitored and stored in the proper place.',
    'Receipts, kitchen phones, and keys were safely stored.',
    'Kitchen utensils were properly stored.',
    'The stove was turned off after cooking.',
    'Properly disposed of the garbage.',
    'Properly washed the burner.',
    // General Cleaning
    'Wiped and arranged the chiller.',
    'Cleaned the canal after cooking.',
    'Arranged the freezer.'
  ];
  $kitchenCount = 14;
  $days = ['mon','tue','wed','thu','fri','sat','sun'];
@endphp
<div class="container py-5">
  <h2 class="dashboard-title mb-4" style="text-align:center;">{{ $category->name }} - Task Description</h2>
  <div class="d-flex justify-content-center">
    <div style="width:100%; max-width:1200px;">
      <form method="POST" action="{{ route('categories.update', $category->id) }}">
        @csrf
        @method('PUT')
        <table class="table table-bordered align-middle" style="background:white; border:2px solid #222;">
          <thead>
            <tr>
              <th colspan="10" style="text-align:left; border:2px solid #222;">DATE:__________________________</th>
              <th colspan="10" style="text-align:left; border:2px solid #222;">DATE:__________________________</th>
            </tr>
            <tr>
              <th rowspan="2" style="background:#00b050; color:white; text-align:center; vertical-align:middle; width:4%; font-size:1.1em; border:2px solid #222;">KITCHEN<br><span style="font-size:0.8em; font-weight:400;">2-3 unchecked=<br>for improvement<br>5 or more=<br>for consequence</span></th>
              <th colspan="8" style="text-align:center; border:2px solid #222;">TASKS TO COMPLETE</th>
              <th rowspan="2" style="color:#e74c3c; text-align:center; vertical-align:middle; border:2px solid #222;">REMARKS</th>
              <th rowspan="2" style="background:#00b050; color:white; text-align:center; vertical-align:middle; width:4%; font-size:1.1em; border:2px solid #222;">General Cleaning</th>
              <th colspan="8" style="text-align:center; border:2px solid #222;">TASKS TO COMPLETE</th>
              <th rowspan="2" style="color:#e74c3c; text-align:center; vertical-align:middle; border:2px solid #222;">REMARKS</th>
            </tr>
            <tr>
              <th style="width:30%; border:2px solid #222;">Description</th>
              @foreach($days as $day)
                <th style="width:3%; border:2px solid #222;">{{ strtoupper($day) }}</th>
              @endforeach
              <th style="width:30%; border:2px solid #222;">Description</th>
              @foreach($days as $day)
                <th style="width:3%; border:2px solid #222;">{{ strtoupper($day) }}</th>
              @endforeach
            </tr>
          </thead>
          <tbody>
            @for($i = 0; $i < max($kitchenCount, count($tasks) - $kitchenCount); $i++)
            <tr>
              <td style="background:#00b050; color:white; border:2px solid #222; @if($i >= $kitchenCount)background:#e2fbe2; color:#222;@endif">
                @if($i < $kitchenCount){{ $tasks[$i] }}@endif
              </td>
              @if($i < $kitchenCount)
                @foreach($days as $day)
                  <td style="border:2px solid #222;">
                    <select name="checks[{{ $i }}][{{ $day }}]" class="form-select form-select-sm">
                      <option value=""></option>
                      <option value="check">✔</option>
                      <option value="wrong">✗</option>
                    </select>
                  </td>
                @endforeach
                <td style="border:2px solid #222;"><input type="text" name="remarks[{{ $i }}]" class="form-control form-control-sm"></td>
              @else
                <td colspan="9" style="border:none; background:transparent;"></td>
              @endif
              <td style="background:#00b050; color:white; border:2px solid #222; @if($i < $kitchenCount)background:#e2fbe2; color:#222;@endif">
                @if($i >= $kitchenCount){{ $tasks[$i] }}@endif
              </td>
              @if($i >= $kitchenCount)
                @foreach($days as $day)
                  <td style="border:2px solid #222;">
                    <select name="checks[{{ $i }}][{{ $day }}]" class="form-select form-select-sm">
                      <option value=""></option>
                      <option value="check">✔</option>
                      <option value="wrong">✗</option>
                    </select>
                  </td>
                @endforeach
                <td style="border:2px solid #222;"><input type="text" name="remarks[{{ $i }}]" class="form-control form-control-sm"></td>
              @else
                <td colspan="9" style="border:none; background:transparent;"></td>
              @endif
            </tr>
            @endfor
          </tbody>
        </table>
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('categories.show', $category->id) }}" class="btn btn-success mt-3">Result</a>
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary mt-3 ms-2">Cancel</a>
      </form>
    </div>
  </div>
</div>
@endsection