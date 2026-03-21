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
            // Make grade_submission_id nullable since it's not always needed
            $table->unsignedBigInteger('grade_submission_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Revert grade_submission_id to not nullable (if needed)
            $table->unsignedBigInteger('grade_submission_id')->nullable(false)->change();
        });
    }
};
