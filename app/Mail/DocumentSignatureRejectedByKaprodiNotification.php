<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentSignatureRejectedByKaprodiNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $approvalRequest;
    public $documentSignature;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct($approvalRequest, $documentSignature, $rejectionReason = null)
    {
        $this->approvalRequest = $approvalRequest;
        $this->documentSignature = $documentSignature;
        $this->rejectionReason = $rejectionReason ?? 'Penempatan tanda tangan perlu diperbaiki';
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "⚠️ Tanda Tangan Perlu Diperbaiki - {$documentName}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.document_signature_rejected_by_kaprodi',
            with: [
                'approvalRequest' => $this->approvalRequest,
                'documentSignature' => $this->documentSignature,
                'rejectionReason' => $this->rejectionReason,
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
        return ['signature-rejected', 're-sign-needed'];
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
