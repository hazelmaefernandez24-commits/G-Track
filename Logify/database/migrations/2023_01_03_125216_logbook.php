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
            $table->string('student_id');
            $table->date('academic_date');
            $table->time('time_out')->nullable();
            $table->string('time_out_remark')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->boolean('monitor_logged_out')->default(false);
            $table->time('time_in')->nullable();
            $table->string('time_in_remark')->nullable();
            $table->string('educator_consideration')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->boolean('monitor_logged_in')->default(false);
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
            $table->string('student_id');
            $table->date('going_out_date');
            $table->string('destination')->nullable();
            $table->string('purpose')->nullable();
            $table->time('time_out')->nullable();
            $table->string('time_out_remark')->nullable();
            $table->string('time_out_consideration')->nullable();
            $table->string('time_out_reason')->nullable();
            $table->boolean('monitor_logged_out')->default(false);
            $table->time('time_in')->nullable();
            $table->string('time_in_remark')->nullable();
            $table->string('educator_consideration')->nullable();
            $table->string('time_in_reason')->nullable();
            $table->boolean('monitor_logged_in')->default(false);
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
            $table->string('visitor_pass');
            $table->string('visitor_name');
            $table->string('valid_id');
            $table->string('id_number');
            $table->string('relationship');
            $table->string('purpose');
            $table->date('visitor_date');
            $table->time('time_in');
            $table->time('time_out')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_deleted')->default(false);
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
    }
};
