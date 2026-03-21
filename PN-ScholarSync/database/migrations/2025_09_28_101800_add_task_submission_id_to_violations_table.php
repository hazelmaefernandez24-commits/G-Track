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
        Schema::table('violations', function (Blueprint $table) {
            $table->unsignedBigInteger('task_submission_id')->nullable()->after('logify_sync_batch_id');
            $table->string('g16_user_id')->nullable()->after('task_submission_id');
            $table->string('recorded_by')->nullable()->after('g16_user_id');
            
            // Add index for better performance
            $table->index('task_submission_id');
            $table->index('g16_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropIndex(['task_submission_id']);
            $table->dropIndex(['g16_user_id']);
            $table->dropColumn(['task_submission_id', 'g16_user_id', 'recorded_by']);
        });
    }
};
