<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;

class NewApprovalRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $approvalRequest;

    /**
     * Create a new message instance.
     */
    public function __construct($approvalRequest)
    {
        $this->approvalRequest = $approvalRequest;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $studentName = $this->approvalRequest->user->name;
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "🔔 Permintaan Baru: {$documentName} - {$studentName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_approval_request',
        );
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
