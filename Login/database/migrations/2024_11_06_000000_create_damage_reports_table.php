<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDamageReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('damage_reports', function (Blueprint $table) {
            $table->id();
            $table->string('location');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->string('item_damaged');
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->string('reporter_contact')->nullable();
            $table->unsignedBigInteger('reported_by');
            $table->string('reporter_name');
            $table->enum('status', ['pending', 'in_progress', 'resolved'])->default('pending');
            $table->timestamp('reported_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['status', 'priority']);
            $table->index(['reported_by']);
            $table->index(['reported_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('damage_reports');
    }
}
