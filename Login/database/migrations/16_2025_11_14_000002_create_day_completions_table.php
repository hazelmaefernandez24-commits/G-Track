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
        if (! Schema::hasTable('day_completions')) {
            Schema::create('day_completions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->string('category_name');
            $table->string('day'); // monday, tuesday, etc.
            $table->date('date'); // specific date
            $table->unsignedBigInteger('completed_by'); // user who marked it complete
            $table->timestamp('completed_at');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['category_id', 'day', 'date']);
            $table->index('completed_by');
            
            // Unique constraint to prevent duplicate completions
            $table->unique(['category_id', 'day', 'date'], 'unique_day_completion');
            
            // Foreign key constraints
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('day_completions');
    }
};
