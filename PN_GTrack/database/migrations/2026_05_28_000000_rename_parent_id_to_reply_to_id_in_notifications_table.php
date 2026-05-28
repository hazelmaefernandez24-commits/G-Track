<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename parent_id → reply_to_id for clarity.
     * parent_id was ambiguous — it could be confused with a student's guardian.
     * reply_to_id clearly expresses the intent: this notification is a reply to another.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('parent_id', 'reply_to_id');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->renameColumn('reply_to_id', 'parent_id');
        });
    }
};
