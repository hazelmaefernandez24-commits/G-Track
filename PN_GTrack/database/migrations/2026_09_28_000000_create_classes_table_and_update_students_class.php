<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create student_classes table
        if (!Schema::hasTable('student_classes')) {
            Schema::create('student_classes', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Seed existing default classes from students table
        $existingClasses = [];
        if (Schema::hasTable('students')) {
            $existingClasses = DB::table('students')->distinct()->pluck('class')->filter()->toArray();
        }
        $defaults = array_unique(array_merge(['2026', '2027', '2028'], $existingClasses));

        foreach ($defaults as $className) {
            $cleanName = trim((string)$className);
            if ($cleanName !== 'All Classes' && !empty($cleanName)) {
                DB::table('student_classes')->updateOrInsert(
                    ['name' => $cleanName],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        // 3. Alter students.class from ENUM to VARCHAR(255) so new custom classes can be stored
        try {
            DB::statement("ALTER TABLE students MODIFY COLUMN class VARCHAR(255) NOT NULL DEFAULT '2026'");
        } catch (\Throwable $e) {
            // Ignore if already altered or not supported
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_classes');
    }
};
