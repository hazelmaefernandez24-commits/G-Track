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
        Schema::create('logify_absent_records', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('batch', 20);
            $table->string('group', 10)->nullable();
            $table->string('month', 2);
            $table->string('year', 4);
            $table->integer('academic_absent_count')->default(0);
            $table->string('sync_batch_id');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['student_id', 'month', 'year']);
            $table->index('sync_batch_id');
            $table->index('last_synced_at');
            
            // Unique constraint to prevent duplicate records for same student/month/year
            $table->unique(['student_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logify_absent_records');
    }
};
