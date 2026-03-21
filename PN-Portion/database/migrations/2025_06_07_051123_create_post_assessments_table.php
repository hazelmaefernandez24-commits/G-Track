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
        // Create post_assessments table for meal evaluation after service
        if (!Schema::hasTable('post_assessments')) {
            Schema::create('post_assessments', function (Blueprint $table) {
                $table->id();
                $table->date('date');
                $table->string('meal_type'); // breakfast, lunch, dinner
                $table->string('meal_name')->nullable(); // Name provided by kitchen
                $table->unsignedBigInteger('menu_id')->nullable();
                $table->integer('planned_portions')->default(0);
                $table->integer('actual_portions_served')->default(0);
                $table->integer('leftover_portions')->default(0);
                $table->decimal('food_waste_kg', 8, 2)->default(0);
                $table->decimal('cost_per_portion', 8, 2)->default(0);
                $table->decimal('total_cost', 10, 2)->default(0);
                $table->integer('student_satisfaction_avg')->nullable(); // 1-5 rating
                $table->text('notes')->nullable();
                $table->text('improvements')->nullable();
                $table->boolean('is_completed')->default(false);
                $table->string('assessed_by');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('menu_id')->references('id')->on('menus')->onDelete('set null');
                $table->foreign('assessed_by')->references('user_id')->on('pnph_users');
                $table->index(['date', 'meal_type']);
                $table->unique(['date', 'meal_type']); // One assessment per meal per day
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_assessments');
    }
};
