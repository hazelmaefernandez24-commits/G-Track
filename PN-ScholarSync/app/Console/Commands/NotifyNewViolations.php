<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Violation;
use App\Models\Notification;
use App\Models\StudentDetails;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;

class NotifyNewViolations extends Command
{
	protected $signature = 'logify:notify-new-violations {--since= : ISO datetime to look back from} {--detailed : Show detailed output}';

	protected $description = 'Create notifications for violations that do not yet have one (supports DB-triggered records).';

	public function handle()
	{
		$since = $this->option('since') ?: now()->subDays(7)->toDateTimeString();
		$this->info("Scanning violations since {$since}...");

		$violations = Violation::where('created_at', '>=', $since)
			->orderBy('created_at', 'desc')
			->get();

		$created = 0; $skipped = 0; $errors = 0;

		foreach ($violations as $violation) {
			try {
				$studentDetails = StudentDetails::where('student_id', $violation->student_id)->first();
				if (!$studentDetails) { $skipped++; continue; }
				$user = User::where('user_id', $studentDetails->user_id)->first();
				if (!$user) { $skipped++; continue; }

				$existing = Notification::where('user_id', $user->user_id)
					->where('related_id', $violation->id)
					->where('type', 'warning')
					->first();
				if ($existing) { $skipped++; continue; }

				$violation->load('violationType');
				$title = 'New Violation Recorded';
				$name = $violation->violationType->violation_name ?? 'Violation';
				$date = $violation->violation_date ? date('M j, Y', strtotime($violation->violation_date)) : 'Today';
				$message = "A new violation has been recorded against you: {$name} on {$date}. Please review it in ScholarSync.";

				NotificationController::createNotification(
					$user->user_id,
					$title,
					$message,
					'warning',
					$violation->id
				);
				$created++;

				if ($this->option('detailed')) {
					$this->line(" + Notification created for violation #{$violation->id} (student {$violation->student_id})");
				}
			} catch (\Exception $e) {
				$errors++;
				Log::error('NotifyNewViolations error', ['violation_id' => $violation->id, 'error' => $e->getMessage()]);
			}
		}

		$this->table(['Metric','Count'], [
			['Notifications Created', $created],
			['Skipped (existing/missing user)', $skipped],
			['Errors', $errors],
			['Scanned Since', $since],
		]);

		$this->info('Done.');
		return Command::SUCCESS;
	}
}
