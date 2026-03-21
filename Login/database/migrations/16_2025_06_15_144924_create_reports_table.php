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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('photo_path')->nullable();
            $table->date('report_date');
            $table->string('title');
            $table->text('comment');
            // Include 'pending' as a valid status so controller and DB agree
            $table->enum('status', ['pending', 'active', 'resolved'])->default('active');
            $table->date('date_resolved')->nullable();
            $table->string('staff_in_charge');
            $table->string('area');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};