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
        Schema::create('academics', function (Blueprint $table) {
            $table->id();
            $table->string('semester_id')->nullable();
            $table->string('student_id')->nullable();
            $table->date('academic_date')->nullable();
            $table->time('time_out')->nullable();
            $table->string('time_out_remark')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->string('time_out_absent_validation')->nullable(); // 'valid', 'not_valid', or null
            $table->boolean('monitor_logged_out')->default(false);
            $table->string('time_out_monitor_name')->nullable();
            $table->time('time_in')->nullable();
            $table->string('time_in_remark')->nullable();
            $table->string('educator_consideration')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->string('time_in_absent_validation')->nullable(); // 'valid', 'not_valid', or null
            $table->boolean('monitor_logged_in')->default(false);
            $table->string('time_in_monitor_name')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);

            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->index('student_id');
        });

        Schema::create('going_outs', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->nullable();
            $table->date('going_out_date')->nullable();
            $table->string('destination')->nullable();
            $table->string('purpose')->nullable();
            $table->time('time_out')->nullable();
            $table->string('time_out_remark')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->boolean('monitor_logged_out')->default(false);
            $table->string('time_out_monitor_name')->nullable();
            $table->time('time_in')->nullable();
            $table->string('time_in_remark')->nullable();
            $table->string('educator_consideration')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->boolean('monitor_logged_in')->default(false);
            $table->string('time_in_monitor_name')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);

            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->index('student_id');
        });

        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->integer('guard_id')->nullable();
            $table->string('visitor_pass')->nullable();
            $table->string('visitor_name')->nullable();
            $table->string('valid_id')->nullable();
            $table->string('id_number')->nullable();
            $table->string('relationship')->nullable();
            $table->string('purpose')->nullable();
            $table->date('visit_date')->nullable();
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();

            // Manual entry fields
            $table->boolean('is_manual_entry')->default(false);
            $table->enum('manual_entry_type', ['time_in', 'time_out', 'both'])->nullable();
            $table->text('manual_entry_reason')->nullable();
            $table->string('manual_entry_monitor')->nullable();
            $table->timestamp('manual_entry_timestamp')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Consideration fields (already exist in model fillable)
            $table->string('consideration')->nullable();
            $table->text('reason')->nullable();
            $table->string('monitor_name')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);
        });

        Schema::create('intern_log', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->date('date');
            $table->time('time_out')->nullable();
            $table->string('time_out_remark')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->string('time_out_absent_validation')->nullable(); // 'valid', 'not_valid', or null
            $table->boolean('monitor_logged_out')->default(false);
            $table->string('time_out_monitor_name')->nullable();
            $table->time('time_in')->nullable();
            $table->string('time_in_remark')->nullable();
            $table->string('educator_consideration')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->string('time_in_absent_validation')->nullable(); // 'valid', 'not_valid', or null
            $table->boolean('monitor_logged_in')->default(false);
            $table->string('time_in_monitor_name')->nullable();

            // Manual entry fields
            $table->boolean('is_manual_entry')->default(false);
            $table->enum('manual_entry_type', ['time_in', 'time_out', 'both'])->nullable();
            $table->text('manual_entry_reason')->nullable();
            $table->string('manual_entry_monitor')->nullable();
            $table->timestamp('manual_entry_timestamp')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Consideration fields (already exist in model fillable)
            $table->string('consideration')->nullable();
            $table->text('reason')->nullable();
            $table->string('monitor_name')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);

            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->index('student_id');
        });

        Schema::create('going_home', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->nullable();
            $table->string('schedule_name')->nullable();
            $table->date('date_time_out')->nullable();
            $table->time('time_out')->nullable();
            $table->string('time_out_remarks')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_monitor_name')->nullable();
            $table->date('date_time_in')->nullable();
            $table->time('time_in')->nullable();
            $table->string('time_in_remarks')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->string('time_in_consideration')->nullable();
            $table->string('time_in_monitor_name')->nullable();

            // Manual entry fields
            $table->boolean('is_manual_entry')->default(false);
            $table->enum('manual_entry_type', ['time_in', 'time_out', 'both'])->nullable();
            $table->text('manual_entry_reason')->nullable();
            $table->string('manual_entry_monitor')->nullable();
            $table->timestamp('manual_entry_timestamp')->nullable();
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();

            // Consideration fields (already exist in model fillable)
            $table->string('consideration')->nullable();
            $table->text('reason')->nullable();
            $table->string('monitor_name')->nullable();

            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);

            $table->foreign('student_id')->references('student_id')->on('student_details')->onDelete('cascade');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
        Schema::dropIfExists('going_outs');
        Schema::dropIfExists('academics');
        Schema::dropIfExists('intern_log');
    }
};
