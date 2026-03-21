<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id('schedule_id');
                $table->string('student_id')->nullable();
                $table->integer('semester_id')->nullable();
                $table->string('batch')->nullable();
                $table->enum('pn_group', ['PN1','PN2'])->nullable();
                $table->string('gender')->nullable();
                $table->enum('day_of_week', [
                    'Monday','Tuesday','Wednesday',
                    'Thursday','Friday','Saturday','Sunday'
                ])->nullable();
                $table->enum('schedule_type', ['academic', 'going_out', 'going_home', 'unique_leisure'])->default('academic')->comment('Type of schedule: academic for irregular academic schedules, going_out for individual going-out schedules');
                $table->time('time_out');
                $table->time('time_in');
                $table->integer('grace_period_logout_minutes')->nullable()->comment('Grace period in minutes for log out (academic schedules only)');
                $table->integer('grace_period_login_minutes')->nullable()->comment('Grace period in minutes for log in (academic schedules only)');
                $table->string('created_by');
                $table->timestamp('created_at');
                $table->string('updated_by')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->boolean('is_deleted')->default(false);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
