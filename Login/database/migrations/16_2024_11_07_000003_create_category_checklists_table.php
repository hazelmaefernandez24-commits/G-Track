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
        // Skip creating the table if it already exists. This makes local
        // iterative migration runs safe when some tables were created in a
        // partial run or by manual SQL.
        if (! Schema::hasTable('category_checklists')) {
            Schema::create('category_checklists', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id');
                $table->text('checklist_items'); // JSON array of checklist items
                $table->timestamps();

                // Create index now; add the FK only if the referenced table exists
                // (to avoid migration ordering issues in environments where
                // `categories` is created later).
                $table->index('category_id');
            });

            // Add foreign key if the referenced `categories` table is present.
            if (Schema::hasTable('categories')) {
                Schema::table('category_checklists', function (Blueprint $table) {
                    $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_checklists');
    }
};
