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
        Schema::create('student_allocations', function (Blueprint $table) {
            $table->id();
            $table->string('batch_year'); // e.g., "2025", "2026"
            $table->enum('gender', ['male', 'female']);
            $table->integer('total_count'); // Number of students available
            $table->integer('allocated_count')->default(0); // Number currently allocated
            $table->date('allocation_date'); // Date of allocation
            $table->timestamps();
            
            $table->unique(['batch_year', 'gender', 'allocation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_allocations');
    }
};
