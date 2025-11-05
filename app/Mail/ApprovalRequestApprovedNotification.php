<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalRequestApprovedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 90;

    public $approvalRequest;
    public $qrCodeUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($approvalRequest)
    {
        $this->approvalRequest = $approvalRequest;
        // $this->qrCodeUrl = $qrCodeUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "✅ Permintaan Disetujui - {$documentName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval_request_approved',
            with: [
                'approvalRequest' => $this->approvalRequest,
                'qrCodeUrl' => $this->qrCodeUrl,
            ],
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
