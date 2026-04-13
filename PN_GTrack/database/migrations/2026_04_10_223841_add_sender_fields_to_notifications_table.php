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
            $table->string('sender_type')->default('system')->after('type'); // 'student', 'admin', 'system'
            $table->unsignedBigInteger('parent_id')->nullable()->after('student_id');
            
            // Foreign key to itself
            $table->foreign('parent_id')->references('id')->on('notifications')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['sender_type', 'parent_id']);
        });
    }
};
