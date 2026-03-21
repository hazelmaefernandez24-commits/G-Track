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
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->string('g16_user_id')->nullable()->after('is_temp_password');
            $table->string('student_id')->nullable()->after('g16_user_id');
            $table->string('batch')->nullable()->after('student_id');
            
            // Add indexes for better performance
            $table->index('g16_user_id');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pnph_users', function (Blueprint $table) {
            $table->dropIndex(['g16_user_id']);
            $table->dropIndex(['student_id']);
            $table->dropColumn(['g16_user_id', 'student_id', 'batch']);
        });
    }
};
