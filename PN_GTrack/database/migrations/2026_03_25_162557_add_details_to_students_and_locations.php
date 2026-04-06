<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'name')) {
                $table->string('name')->nullable();
            }
            if (!Schema::hasColumn('students', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('students', 'gender')) {
                $table->string('gender')->default('male');
            }
            if (!Schema::hasColumn('students', 'class')) {
                $table->string('class')->nullable();
            }
            if (!Schema::hasColumn('students', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('students', 'sos_status')) {
                $table->string('sos_status')->default('safe');
            }
        });

        Schema::table('locations', function (Blueprint $table) {
            if (!Schema::hasColumn('locations', 'student_id')) {
                $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            }
            if (!Schema::hasColumn('locations', 'latitude')) {
                $table->decimal('latitude', 10, 6)->nullable();
            }
            if (!Schema::hasColumn('locations', 'longitude')) {
                $table->decimal('longitude', 10, 6)->nullable();
            }
            if (!Schema::hasColumn('locations', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable();
            }
            if (!Schema::hasColumn('locations', 'sos_status')) {
                $table->string('sos_status')->default('safe');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            if (Schema::hasColumn('locations', 'sos_status')) {
                $table->dropColumn('sos_status');
            }
            if (Schema::hasColumn('locations', 'recorded_at')) {
                $table->dropColumn('recorded_at');
            }
            if (Schema::hasColumn('locations', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('locations', 'latitude')) {
                $table->dropColumn('latitude');
            }
            if (Schema::hasColumn('locations', 'student_id')) {
                $table->dropForeign(['student_id']);
                $table->dropColumn('student_id');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'sos_status')) {
                $table->dropColumn('sos_status');
            }
            if (Schema::hasColumn('students', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('students', 'class')) {
                $table->dropColumn('class');
            }
            if (Schema::hasColumn('students', 'gender')) {
                $table->dropColumn('gender');
            }
            if (Schema::hasColumn('students', 'email')) {
                $table->dropColumn('email');
            }
            if (Schema::hasColumn('students', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
