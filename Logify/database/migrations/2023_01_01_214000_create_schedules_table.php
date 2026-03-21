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
                $table->string('batch')->nullable();
                $table->enum('pn_group', ['PN1','PN2'])->nullable();
                $table->string('gender')->nullable();
                $table->enum('day_of_week', [
                    'Monday','Tuesday','Wednesday',
                    'Thursday','Friday','Saturday','Sunday'
                ]);
                $table->time('time_out');
                $table->time('time_in');
                $table->date('valid_until')->nullable();
                $table->integer('grace_period_logout_minutes')->nullable()->comment('Grace period in minutes for log out (academic and going out schedules)');
                $table->integer('grace_period_login_minutes')->nullable()->comment('Grace period in minutes for log in (academic and going out schedules)');
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
