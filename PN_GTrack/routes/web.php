<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    // PN_GTrack currently doesn't have student tracking tables/migrations yet.
    // These values are placeholders to match the requested dashboard design.
    $onlineCount = 13;
    $offlineCount = 3;

    $latestTime = now()->format('g:i:s A');
    $latestDate = now()->format('n/j/Y');

    return view('dashboard', compact('onlineCount', 'offlineCount', 'latestTime', 'latestDate'));
});
