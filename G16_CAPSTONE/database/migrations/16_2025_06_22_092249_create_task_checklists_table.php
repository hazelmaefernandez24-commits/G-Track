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
        Schema::create('task_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('task_category');
            $table->string('task_description');
            $table->date('week_start_date');
            $table->json('week1_status')->nullable(); // [mon, tue, wed, thu, fri, sat, sun]
            $table->json('week2_status')->nullable(); // [mon, tue, wed, thu, fri, sat, sun]
            $table->text('week1_remarks')->nullable();
            $table->text('week2_remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_checklists');
    }
};