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
        Schema::create('task_checklist_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('task_id'); // e.g., 'kitchen1', 'cleaning2', etc.
            $table->date('task_date'); // The specific date (MON, TUE, etc.)
            $table->enum('status', ['check', 'wrong'])->nullable(); // Status: check or wrong
            $table->text('remarks')->nullable(); // Optional remarks
            $table->integer('page_number')->default(1); // Which page (1-10)
            $table->timestamps();
            
            // Ensure unique combination of task_id and task_date
            $table->unique(['task_id', 'task_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_checklist_statuses');
    }
};
