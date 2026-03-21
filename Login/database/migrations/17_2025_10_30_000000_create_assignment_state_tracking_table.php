<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration creates a table to track auto-shuffle assignment states
     * to ensure assignments persist across user login/logout sessions.
     */
    public function up(): void
    {
        Schema::create('assignment_state_tracking', function (Blueprint $table) {
            $table->id();
            
            // Assignment identification
            $table->unsignedBigInteger('assignment_id')->index();
            $table->string('category_name', 100)->index();
            $table->string('assignment_status', 20)->default('current'); // current, previous, archived
            
            // Auto-shuffle tracking
            $table->timestamp('last_shuffle_at')->nullable();
            $table->json('shuffle_requirements')->nullable(); // Store the manual requirements used
            $table->integer('total_members')->default(0);
            $table->json('member_distribution')->nullable(); // Store gender/batch breakdown
            
            // Assignment period
            $table->date('assignment_start_date');
            $table->date('assignment_end_date');
            
            // State persistence flags
            $table->boolean('is_locked')->default(false); // Prevent accidental changes
            $table->boolean('shuffle_allowed')->default(true); // Control when shuffle is allowed
            $table->timestamp('next_shuffle_allowed_at')->nullable();
            
            // Audit trail
            $table->string('created_by_user_id', 50)->nullable();
            $table->string('last_modified_by_user_id', 50)->nullable();
            $table->text('modification_notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['assignment_status', 'category_name'], 'ast_status_category_idx');
            $table->index(['assignment_end_date', 'shuffle_allowed'], 'ast_end_date_shuffle_idx');
            $table->index('last_shuffle_at', 'ast_last_shuffle_idx');
        });
        
        // Insert initial comment
        DB::statement("ALTER TABLE assignment_state_tracking COMMENT = 'Tracks auto-shuffle assignment states to ensure persistence across user sessions and prevent unwanted re-shuffling'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_state_tracking');
    }
};
