<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentSignedByUserNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $approvalRequest;
    public $documentSignature;

    /**
     * Create a new message instance.
     */
    public function __construct($approvalRequest, $documentSignature)
    {
        $this->approvalRequest = $approvalRequest;
        $this->documentSignature = $documentSignature;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $studentName = $this->approvalRequest->user->name;
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "✍️ Dokumen Ditandatangani - Perlu Verifikasi: {$documentName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document_signed_by_user',
            with: [
                'approvalRequest' => $this->approvalRequest,
                'documentSignature' => $this->documentSignature,
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

    /**
     * Get the tags for the message.
     */
    public function tags(): array
    {
        return ['document-signed', 'verification-needed'];
    }

    /**
     * Get the metadata for the message.
     */
    // public function metadata(): array
    // {
    //     return [
    //         'approval_request_id' => $this->approvalRequest->id,
    //         'document_signature_id' => $this->documentSignature->id,
    //         'student_id' => $this->approvalRequest->user_id,
    //     ];
    // }
}
