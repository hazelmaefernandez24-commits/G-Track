<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasMiddleName = Schema::hasColumn('admins', 'middle_name');
        $hasMiddleInitial = Schema::hasColumn('admins', 'middle_initial');

        if (! $hasMiddleInitial) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('middle_initial', 1)->nullable()->after('first_name');
            });
        }

        if ($hasMiddleName) {
            DB::table('admins')
                ->select('id', 'middle_name', 'middle_initial')
                ->orderBy('id')
                ->chunk(100, function ($admins) {
                    foreach ($admins as $admin) {
                        if ($admin->middle_initial === null || $admin->middle_initial === '') {
                            DB::table('admins')
                                ->where('id', $admin->id)
                                ->update([
                                    'middle_initial' => $admin->middle_name === null
                                        ? null
                                        : mb_substr($admin->middle_name, 0, 1),
                                ]);
                        }
                    }
                });

            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('middle_name');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('admins', 'middle_name')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('middle_name')->nullable()->after('first_name');
            });
        }

        if (Schema::hasColumn('admins', 'middle_initial')) {
            DB::table('admins')
                ->select('id', 'middle_initial')
                ->orderBy('id')
                ->chunk(100, function ($admins) {
                    foreach ($admins as $admin) {
                        DB::table('admins')
                            ->where('id', $admin->id)
                            ->update(['middle_name' => $admin->middle_initial]);
                    }
                });

            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('middle_initial');
            });
        }
    }
};
