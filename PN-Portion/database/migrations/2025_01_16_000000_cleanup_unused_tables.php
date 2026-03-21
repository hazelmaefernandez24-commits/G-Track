<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Remove unused/duplicate tables
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();

        // Remove duplicate polling tables (replaced by kitchen_menu_polls)
        Schema::dropIfExists('meal_poll_responses');
        Schema::dropIfExists('meal_polls');
        Schema::dropIfExists('poll_responses');
        Schema::dropIfExists('polls');

        // Remove unused meal status tracking
        Schema::dropIfExists('meal_statuses');

        // Remove redundant menu tables
        Schema::dropIfExists('weekly_menus');
        Schema::dropIfExists('menu_items');

        // Remove unused ingredient system
        Schema::dropIfExists('ingredients');

        // Remove unused supplier system
        Schema::dropIfExists('suppliers');

        // Remove unused logging and settings
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('admin_settings');

        // Remove old order system (replaced by pre_orders)
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');

        // Remove generic reports (replaced by specific report tables)
        Schema::dropIfExists('reports');

        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations - Recreate tables if needed
     */
    public function down(): void
    {
        // Note: This down method is intentionally minimal
        // If you need to restore any of these tables, 
        // refer to the original migration files
        
        // Only recreate essential tables that might be needed
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->constrained();
            $table->timestamps();
        });
    }
};
