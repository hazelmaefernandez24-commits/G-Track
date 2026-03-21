<!--
    Admin Reports View: Copied and adapted from StudentsDashboard/reports.blade.php
    - This file provides the same student report and additional report functionality for admin users.
    - You may need to adjust routes, permissions, and data sources for admin context.
-->
@include('layouts.app')

@section('content')
<!-- BEGIN: Copied Student Report and Additional Report UI -->
<?php /*
    This is a direct copy of the student reports page. You may need to:
    - Replace student-specific routes with admin equivalents
    - Adjust data fetching to allow admin to view/manage all students
    - Add admin-specific controls if needed
*/ ?>
@include('StudentsDashboard.reports')
<!-- END: Copied Student Report and Additional Report UI -->
@endsection
