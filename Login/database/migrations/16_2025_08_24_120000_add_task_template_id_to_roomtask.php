<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (Schema::hasTable('roomtask') && !Schema::hasColumn('roomtask', 'task_template_id')) {
			Schema::table('roomtask', function (Blueprint $table) {
				$table->foreignId('task_template_id')->nullable()->constrained('task_templates')->onDelete('set null');
			});
		}
	}

	public function down(): void
	{
		if (Schema::hasTable('roomtask') && Schema::hasColumn('roomtask', 'task_template_id')) {
			Schema::table('roomtask', function (Blueprint $table) {
				$table->dropConstrainedForeignId('task_template_id');
			});
		}
	}
};

