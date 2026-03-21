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
        // Create weekly meal plans table
        if (!Schema::hasTable('weekly_meal_plans')) {
            Schema::create('weekly_meal_plans', function (Blueprint $table) {
                $table->id();
                $table->integer('week_cycle'); // 1 or 2 (for alternating weeks)
                $table->enum('status', ['draft', 'pending_ingredients', 'approved', 'active', 'completed'])
                      ->default('draft');
                $table->string('created_by');
                $table->date('plan_date');
                $table->datetime('finalized_at')->nullable();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                // Foreign keys
                $table->foreign('created_by')->references('user_id')->on('pnph_users')->onDelete('cascade');
                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('set null');

                // Indexes
                $table->index(['week_cycle', 'status']);
                $table->index('plan_date');
            });
        }

        // Create pivot table for weekly meal plans and meals
        if (!Schema::hasTable('weekly_meal_plan_meals')) {
            Schema::create('weekly_meal_plan_meals', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('weekly_meal_plan_id');
                $table->unsignedBigInteger('meal_id');
                $table->timestamps();

                // Foreign keys
                $table->foreign('weekly_meal_plan_id', 'wmpm_plan_foreign')
                      ->references('id')->on('weekly_meal_plans')->onDelete('cascade');
                $table->foreign('meal_id', 'wmpm_meal_foreign')
                      ->references('id')->on('meals')->onDelete('cascade');

                // Unique constraint to prevent duplicate meal assignments
                $table->unique(['weekly_meal_plan_id', 'meal_id'], 'wmpm_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_meal_plan_meals');
        Schema::dropIfExists('weekly_meal_plans');
    }
};
