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
        Schema::table('notifications', function (Blueprint $table) {
            // Add message column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'message')) {
                $table->text('message')->after('title');
            }

            // Add type column if it doesn't exist
            if (!Schema::hasColumn('notifications', 'type')) {
                $table->enum('type', ['info', 'warning', 'success', 'danger'])->default('info')->after('message');
            }

            // Add related_id column if it doesn't exist (more generic than grade_submission_id)
            if (!Schema::hasColumn('notifications', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['message', 'type', 'related_id']);
        });
    }
};
