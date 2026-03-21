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
        Schema::create('user_dashboard_views', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('data_type'); // e.g., 'recent_orders', 'inventory_reports', 'feedback', etc.
            $table->string('data_identifier'); // unique identifier for the specific data item (e.g., order_id, report_id)
            $table->timestamp('viewed_at');
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('pnph_users')->onDelete('cascade');
            $table->unique(['user_id', 'data_type', 'data_identifier'], 'user_dashboard_views_unique');
            $table->index(['user_id', 'data_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_dashboard_views');
    }
};
