<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasMiddleName = Schema::hasColumn('students', 'middle_name');
        $hasMiddleInitial = Schema::hasColumn('students', 'middle_initial');

        if (! $hasMiddleInitial) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('middle_initial', 1)->nullable()->after('first_name');
            });
        }

        if ($hasMiddleName) {
            DB::table('students')
                ->select('id', 'middle_name', 'middle_initial')
                ->orderBy('id')
                ->chunk(100, function ($students) {
                    foreach ($students as $student) {
                        if ($student->middle_initial === null || $student->middle_initial === '') {
                            DB::table('students')
                                ->where('id', $student->id)
                                ->update([
                                    'middle_initial' => $student->middle_name === null
                                        ? null
                                        : mb_substr($student->middle_name, 0, 1),
                                ]);
                        }
                    }
                });

            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('middle_name');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('students', 'middle_name')) {
            Schema::table('students', function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('first_name');
            });
        }

        if (Schema::hasColumn('students', 'middle_initial')) {
            DB::table('students')
                ->select('id', 'middle_initial')
                ->orderBy('id')
                ->chunk(100, function ($students) {
                    foreach ($students as $student) {
                        DB::table('students')
                            ->where('id', $student->id)
                            ->update(['middle_name' => $student->middle_initial]);
                    }
                });

            Schema::table('students', function (Blueprint $table) {
                $table->dropColumn('middle_initial');
            });
        }
    }
};
