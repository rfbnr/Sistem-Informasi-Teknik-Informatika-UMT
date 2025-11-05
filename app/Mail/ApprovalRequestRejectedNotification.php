<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalRequestRejectedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 90;

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
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "⚠️ Permintaan Perlu Perbaikan - {$documentName}",
            tags: ['digital-signature', 'request-rejected'],
            // metadata: [
            //     'approval_request_id' => $this->approvalRequest->id,
            //     'user_id' => $this->approvalRequest->user_id,
            // ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval_request_rejected',
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
