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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('category', [
                'school_activity',
                'holiday',
                'examination',
                'deadline',
                'vacation',
                'special'
            ])->default('school_activity');
            $table->enum('semester', ['first', 'second', 'summer'])->nullable();
            $table->string('academic_year')->default('2025-2026');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['start_date', 'end_date']);
            $table->index('category');
            $table->index('semester');
            $table->index('academic_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
