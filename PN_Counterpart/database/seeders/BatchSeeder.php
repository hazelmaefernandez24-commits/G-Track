<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Batch;
use App\Models\CustomNotification;
use App\Models\Student;
use Illuminate\Http\Request;

class BatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Batch::create(['batch_year' => 2025, 'total_due' => 50000]);
        Batch::create(['batch_year' => 2026, 'total_due' => 60000]);
    }

    public function updateBatch(Request $request)
    {
        $request->validate([
            'batch_year' => 'required|exists:batches,batch_year',
            'total_due' => 'required|numeric|min:0',
        ]);

        // Find the batch and update the total_due
        $batch = Batch::where('batch_year', $request->batch_year)->first();
        $batch->update(['total_due' => $request->total_due]);

        // Notify all students in the batch
        $students = Student::where('batch_year', $request->batch_year)->get();
        foreach ($students as $student) {
            CustomNotification::create([
                'user_id' => $student->user_id, // Assuming `user_id` is linked to the student
                'type' => 'batch_update',
                'title' => 'Batch Payable Updated',
                'message' => 'The payable amount for your batch (' . $request->batch_year . ') has been updated to ₱' . number_format($request->total_due, 2) . '.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Batch amount updated successfully, and students have been notified!');
    }
}
