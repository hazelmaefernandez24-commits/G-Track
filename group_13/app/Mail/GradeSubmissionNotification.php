<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\GradeSubmission;
use App\Models\PNUser;

class GradeSubmissionNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $gradeSubmission;

    /**
     * Create a new message instance.
     */
    public function __construct(PNUser $student, GradeSubmission $gradeSubmission)
    {
        $this->student = $student;
        $this->gradeSubmission = $gradeSubmission;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Grade Submission Available - Action Required',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.grade-submission-notification',
        );
    }

    /**
     * Build the message (for backward compatibility).
     */
    public function build()
    {
        return $this->subject('PNPh-SAMS: New Grade Submission Available - Action Required')
            ->view('emails.grade-submission-notification');
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
