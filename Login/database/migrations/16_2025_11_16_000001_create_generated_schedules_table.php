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
        // Use the default connection to ensure migrations run in environments
        // where a custom 'login' connection isn't configured. Also guard table
        // creation so migrations are re-runnable.
        if (! Schema::hasTable('generated_schedules')) {
            Schema::create('generated_schedules', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('assignment_id')->nullable();
                $table->string('category_name')->nullable();
                $table->date('schedule_date');
                $table->unsignedBigInteger('student_id')->nullable();
                $table->string('student_name')->nullable();
                $table->string('task_title')->nullable();
                $table->text('task_description')->nullable();
                $table->string('batch')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->string('rotation_frequency')->default('Daily');
                $table->json('schedule_data')->nullable();
                $table->timestamps();

                $table->index('assignment_id');
                $table->index('student_id');
                $table->index('schedule_date');
                $table->index('batch');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('generated_schedules');
    }
};
