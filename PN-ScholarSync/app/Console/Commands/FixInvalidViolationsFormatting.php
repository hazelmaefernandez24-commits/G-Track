<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInvalidViolationsFormatting extends Command
{
    protected $signature = 'fix:invalid-violations-format';
    protected $description = 'Normalize invalid_violations rows to match formatting rules (offense count, pending consequence/status, incident_details text, prepared_by null when pending)';

    public function handle()
    {
        $this->info('Starting invalid_violations formatting fix...');
        DB::beginTransaction();
        try {
            // 1) offense = total violations count per student
            $this->info('Updating offense with total violations count per student...');
            DB::statement(<<<SQL
                UPDATE invalid_violations iv
                LEFT JOIN (
                  SELECT student_id, COUNT(*) AS vcount
                  FROM violations
                  GROUP BY student_id
                ) v ON v.student_id = iv.student_id
                SET iv.offense = IFNULL(v.vcount, 0)
            SQL);

            // 2) consequence = 'pending' where status is pending
            $this->info("Setting consequence = 'pending' for pending rows...");
            DB::table('invalid_violations')
                ->where('status', 'pending')
                ->update(['consequence' => 'pending']);

            // 3) incident_details = 'Validated by admin.' for all rows
            $this->info("Setting incident_details to 'Validated by admin.'...");
            DB::table('invalid_violations')->update(['incident_details' => 'Validated by admin.']);

            // 4) prepared_by = NULL when pending
            $this->info('Nulling prepared_by for pending rows...');
            DB::table('invalid_violations')
                ->where('status', 'pending')
                ->update(['prepared_by' => null]);

            // 5) status = 'pending' if corresponding violation (same task_submission_id) is pending
            $this->info("Syncing status from violations where task_submission_id matches and is pending...");
            DB::statement(<<<SQL
                UPDATE invalid_violations iv
                INNER JOIN violations v
                  ON v.task_submission_id = iv.task_submission_id
                SET iv.status = 'pending'
                WHERE v.status = 'pending'
            SQL);

            // Ensure consequence_status aligns as pending when status is pending
            $this->info('Setting consequence_status to pending where status is pending...');
            DB::table('invalid_violations')
                ->where('status', 'pending')
                ->update(['consequence_status' => 'pending']);

            DB::commit();
            $this->info('Done.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
