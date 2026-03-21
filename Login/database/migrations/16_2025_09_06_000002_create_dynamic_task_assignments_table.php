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
        Schema::create('dynamic_task_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('dynamic_task_categories')->onDelete('cascade');
            $table->string('assignment_name');
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['pending', 'current', 'completed', 'cancelled'])->default('pending');
            $table->string('created_by'); // Admin user who created the assignment
            $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->timestamps();
            
            // Index for performance
            $table->index(['category_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dynamic_task_assignments');
    }
};
