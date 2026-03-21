<?php
// studentManager.php

// Master student list
$students = getAllStudents(); // Fetch from DB or persistent storage

function addStudent($studentName) {
    global $students;
    // Add to master list
    $students[] = $studentName;
    saveAllStudents($students); // Save to DB or persistent storage
}

// Get students for today's assignment
function getStudentsForToday() {
    global $students;
    return $students; // Always use the updated master list
}

// Assign tasks to students
function assignTasks($tasks) {
    $students = getStudentsForToday();
    // Shuffle and assign tasks as needed
    shuffle($students);
    // ...assignment logic...
}

// Checklist generation
function getChecklist() {
    global $students;
    return $students; // Checklist includes all students
}