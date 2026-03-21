<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\FinanceController;
use App\Models\FinanceSetting;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'payment:send-reminders {--force : Force send reminders even if auto-reminders are disabled} {--test : Show preview without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send payment reminders to students with overdue payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('test')) {
            return $this->handleTest();
        }

        $this->info('Starting payment reminder process...');

        try {
            // Check if payment reminders are enabled
            $reminderSettings = FinanceSetting::getPaymentReminderSettings();
            
            if (!$reminderSettings['auto_enabled'] && !$this->option('force')) {
                $this->warn('Automatic payment reminders are disabled in settings. Use --force to override.');
                return 1;
            }

            // Create an instance of FinanceController to use its methods
            $financeController = new FinanceController();
            
            // Call the sendPaymentReminders method
            $response = $financeController->sendPaymentReminders();
            $responseData = $response->getData(true);

            if ($responseData['success']) {
                $this->info($responseData['message']);
                $this->info("Reminders sent: {$responseData['reminders_sent']}");
                $this->info("Total overdue students: {$responseData['total_overdue']}");
                
                if (!empty($responseData['errors'])) {
                    $this->warn('Some errors occurred:');
                    foreach ($responseData['errors'] as $error) {
                        $this->error($error);
                    }
                }
            } else {
                $this->error($responseData['message']);
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Error sending payment reminders: ' . $e->getMessage());
            \Log::error('Payment reminder command error: ' . $e->getMessage());
            return 1;
        }

        $this->info('Payment reminder process completed successfully.');
        return 0;
    }

    
}
