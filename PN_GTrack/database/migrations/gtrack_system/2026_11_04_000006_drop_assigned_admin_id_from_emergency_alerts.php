<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('gtrack_system');

        if ($schema->hasColumn('emergency_alerts', 'assigned_admin_id')) {
            $schema->table('emergency_alerts', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('assigned_admin_id');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('gtrack_system');

        if (!$schema->hasColumn('emergency_alerts', 'assigned_admin_id')) {
            $schema->table('emergency_alerts', function (Blueprint $table): void {
                $table->foreignId('assigned_admin_id')->nullable()->constrained('admins')->nullOnDelete();
            });
        }
    }
};
