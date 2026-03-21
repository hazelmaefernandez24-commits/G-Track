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
        Schema::table('task_checklist_statuses', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['task_id', 'task_date']);
            
            // Add new unique constraint that includes page_number
            $table->unique(['task_id', 'task_date', 'page_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_checklist_statuses', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique(['task_id', 'task_date', 'page_number']);
            
            // Restore the old unique constraint
            $table->unique(['task_id', 'task_date']);
        });
    }
};
