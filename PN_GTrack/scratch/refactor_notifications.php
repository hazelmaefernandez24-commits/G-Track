<?php
$file = 'c:/Users/fernandez/Desktop/PNSystem/PN_Systems/PN_GTrack/resources/views/notifications.blade.php';
$content = file_get_contents($file);

// Extract styles between <style> and </style>
preg_match('/<style>(.*?)<\/style>/s', $content, $matches);
$styles = isset($matches[1]) ? $matches[1] : '';

// Remove everything from <!DOCTYPE html> to </header>
$content = preg_replace('/<!DOCTYPE html>.*?<\/header>/s', '', $content);

// Remove the top container div and stats cards
$content = preg_replace('/<main class="container">.*?<div class="page-title" style="margin-top: 32px;">/s', '<div class="page-title" style="margin-bottom: 24px;">', $content);

// Remove trailing </body></html> and </main>
$content = str_replace(['</body>', '</html>', '</main>'], '', $content);

$newContent = "@extends('layouts.app')\n\n@section('title', 'Notifications')\n@section('subtitle', 'Send and manage notifications')\n\n@push('styles')\n<style>" . $styles . "\n</style>\n<!-- Quill Rich Text Editor -->\n<link href=\"https://cdn.quilljs.com/1.3.6/quill.snow.css\" rel=\"stylesheet\">\n<script src=\"https://cdn.quilljs.com/1.3.6/quill.min.js\"></script>\n@endpush\n\n@section('content')\n" . $content . "\n@endsection\n";

file_put_contents($file, $newContent);
echo 'Refactoring complete.';
