<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BatchUpdateNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $batch;
    private $oldAmount;
    private $newAmount;

    public function __construct($batch, $oldAmount, $newAmount)
    {
        $this->batch = $batch;
        $this->oldAmount = $oldAmount;
        $this->newAmount = $newAmount;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Batch Payment Amount Update')
            ->greeting('Dear Batch ' . $this->batch->batch_year . ' Students,')
            ->line('Please be informed that the payable amount for Parents\' Counterpart for your batch has been updated.')
            ->line('Previous Amount: ₱' . number_format($this->oldAmount, 2))
            ->line('New Amount: ₱' . number_format($this->newAmount, 2))
            ->line('Kindly review the updated details at your earliest convenience.')
            ->line('Should you have any questions or require further clarification, please do not hesitate to reach out to the finance office.')
            ->line('Best regards,')
            ->line('Finance Department');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'batch_update',
            'message' => 'The payable amount for Parents\' Counterpart for Batch ' . $this->batch->batch_year . ' has been updated from ₱' . number_format($this->oldAmount, 2) . ' to ₱' . number_format($this->newAmount, 2) . '.',
            'batch_year' => $this->batch->batch_year,
            'old_amount' => $this->oldAmount,
            'new_amount' => $this->newAmount
        ];
    }
} 