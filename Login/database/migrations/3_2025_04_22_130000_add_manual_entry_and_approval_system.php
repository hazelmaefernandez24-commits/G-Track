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
        // Add manual entry tracking columns to academics table
        Schema::table('academics', function (Blueprint $table) {
            $table->boolean('is_manual_entry')->default(false)->after('monitor_logged_in');
            $table->string('manual_entry_type')->nullable()->after('is_manual_entry'); // 'time_out', 'time_in', 'both'
            $table->string('manual_entry_reason')->nullable()->after('manual_entry_type');
            $table->string('manual_entry_monitor')->nullable()->after('manual_entry_reason');
            $table->timestamp('manual_entry_timestamp')->nullable()->after('manual_entry_monitor');

            // Approval workflow columns
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('manual_entry_timestamp');
            $table->string('approved_by')->nullable()->after('approval_status'); // Educator name
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        // Add manual entry tracking columns to going_outs table
        Schema::table('going_outs', function (Blueprint $table) {
            $table->boolean('is_manual_entry')->default(false)->after('monitor_logged_in');
            $table->string('manual_entry_type')->nullable()->after('is_manual_entry'); // 'time_out', 'time_in', 'both'
            $table->string('manual_entry_reason')->nullable()->after('manual_entry_type');
            $table->string('manual_entry_monitor')->nullable()->after('manual_entry_reason');
            $table->timestamp('manual_entry_timestamp')->nullable()->after('manual_entry_monitor');

            // Approval workflow columns
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->nullable()->after('manual_entry_timestamp');
            $table->string('approved_by')->nullable()->after('approval_status'); // Educator name
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('approval_notes')->nullable()->after('approved_at');
        });

        // Create manual_entry_logs table for detailed tracking
        Schema::create('manual_entry_logs', function (Blueprint $table) {
            $table->id();
            $table->string('student_id')->nullable();
            $table->enum('log_type', ['academic', 'going_out', 'visitor', 'going_home', 'intern']);
            $table->bigInteger('log_id')->nullable();
            $table->enum('entry_type', ['time_out', 'time_in', 'both', 'absent']);
            $table->text('reason');
            $table->string('monitor_name');
            $table->json('original_data')->nullable(); // Store original values before manual entry
            $table->json('manual_data'); // Store manually entered values
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('student_id')->on('student_details');
            $table->index(['student_id', 'log_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_entry_logs');

        Schema::table('going_outs', function (Blueprint $table) {
            $table->dropColumn([
                'is_manual_entry',
                'manual_entry_type',
                'manual_entry_reason',
                'manual_entry_monitor',
                'manual_entry_timestamp',
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes'
            ]);
        });

        Schema::table('academics', function (Blueprint $table) {
            $table->dropColumn([
                'is_manual_entry',
                'manual_entry_type',
                'manual_entry_reason',
                'manual_entry_monitor',
                'manual_entry_timestamp',
                'approval_status',
                'approved_by',
                'approved_at',
                'approval_notes'
            ]);
        });
    }
};
