<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\GradeSubmission;
use App\Models\GradeSubmissionProof;
use App\Models\School;
use App\Models\ClassModel;
use App\Models\PNUser;
use App\Models\Subject;

class DummyGradesSeeder extends Seeder
{
    public function run(): void
    {
        // Find University of San Jose Recoletos
        $school = School::where('name', 'LIKE', '%University of San Jose%')->first();
        
        if (!$school) {
            $this->command->error('University of San Jose Recoletos not found in database');
            return;
        }

        $this->command->info("Found school: {$school->name} (ID: {$school->school_id})");

        // Get classes for this school
        $classes = ClassModel::where('school_id', $school->school_id)->get();
        
        if ($classes->isEmpty()) {
            $this->command->error('No classes found for University of San Jose Recoletos');
            return;
        }

        $this->command->info("Found {$classes->count()} classes");

        // Get subjects for this school
        $subjects = Subject::where('school_id', $school->school_id)->get();
        
        if ($subjects->isEmpty()) {
            $this->command->error('No subjects found for University of San Jose Recoletos');
            return;
        }

        $this->command->info("Found {$subjects->count()} subjects");

        // Create grade submissions for each class
        foreach ($classes as $class) {
            $this->command->info("Processing class: {$class->class_name}");
            
            // Get students in this class
            $students = $class->students()->where('user_role', 'student')->get();
            
            if ($students->isEmpty()) {
                $this->command->warn("No students found in class: {$class->class_name}");
                continue;
            }

            $this->command->info("Found {$students->count()} students in {$class->class_name}");

            // Create grade submission for this class
            $gradeSubmission = GradeSubmission::firstOrCreate([
                'school_id' => $school->school_id,
                'class_id' => $class->class_id,
                'semester' => '1st',
                'term' => 'prelim',
                'academic_year' => '2024-2025',
            ], [
                'status' => 'pending',
                'subject_ids' => json_encode($subjects->pluck('id')->toArray())
            ]);

            $this->command->info("Grade submission created/found: ID {$gradeSubmission->id}");

            // Attach subjects to the grade submission if not already attached
            $existingSubjects = $gradeSubmission->subjects()->pluck('subjects.id')->toArray();
            $subjectsToAttach = $subjects->pluck('id')->diff($existingSubjects)->toArray();
            
            if (!empty($subjectsToAttach)) {
                $gradeSubmission->subjects()->attach($subjectsToAttach);
                $this->command->info("Attached " . count($subjectsToAttach) . " subjects to grade submission");
            }

            // Generate dummy grades using the proper 1.0-5.0 scale with special grades
            $dummyGrades = [
                // Excellent grades (1.0-1.75)
                '1.0', '1.25', '1.5', '1.75',
                // Good grades (2.0-2.75)
                '2.0', '2.25', '2.5', '2.75',
                // Satisfactory grades (3.0-3.75)
                '3.0', '3.25', '3.5', '3.75',
                // Poor grades (4.0-5.0)
                '4.0', '4.25', '4.5', '4.75', '5.0',
                // Special grades
                'INC', 'DR', 'NC'
            ];

            $studentsProcessed = 0;
            
            foreach ($students as $student) {
                $this->command->info("Processing student: {$student->user_fname} {$student->user_lname} (ID: {$student->user_id})");
                
                foreach ($subjects as $subject) {
                    // Check if grade already exists
                    $existingGrade = DB::table('grade_submission_subject')
                        ->where('grade_submission_id', $gradeSubmission->id)
                        ->where('user_id', $student->user_id)
                        ->where('subject_id', $subject->id)
                        ->first();
                    
                    if (!$existingGrade) {
                        // Insert dummy grade
                        $randomGrade = $dummyGrades[array_rand($dummyGrades)];
                        
                        DB::table('grade_submission_subject')->insert([
                            'grade_submission_id' => $gradeSubmission->id,
                            'subject_id' => $subject->id,
                            'user_id' => $student->user_id,
                            'grade' => $randomGrade,
                            'status' => 'submitted',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        $this->command->info("  - Added grade {$randomGrade} for subject: {$subject->name}");
                    }
                }
                
                // Create dummy proof if not exists
                $existingProof = GradeSubmissionProof::where('grade_submission_id', $gradeSubmission->id)
                    ->where('user_id', $student->user_id)
                    ->first();
                
                if (!$existingProof) {
                    GradeSubmissionProof::create([
                        'grade_submission_id' => $gradeSubmission->id,
                        'user_id' => $student->user_id,
                        'file_path' => 'dummy/proof_' . $student->user_id . '_' . time() . '.jpg',
                        'file_name' => 'dummy_proof_' . $student->user_id . '.jpg',
                        'file_type' => 'image/jpeg',
                        'status' => 'pending'
                    ]);
                    
                    $this->command->info("  - Created dummy proof for student");
                }
                
                $studentsProcessed++;
            }
            
            $this->command->info("Processed {$studentsProcessed} students for class {$class->class_name}");
        }

        $this->command->info('Dummy grades and proofs seeding completed for University of San Jose Recoletos!');
    }
}
