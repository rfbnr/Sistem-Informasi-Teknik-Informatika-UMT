<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApprovalRequestSignedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $approvalRequest;
    public $documentSignature;
    public $qrCodeBase64;
    public $qrCodeUrl;
    public $verificationUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($approvalRequest, $documentSignature = null)
    {
        $this->approvalRequest = $approvalRequest;
        $this->documentSignature = $documentSignature ?? $approvalRequest->documentSignature;

        // Prepare QR Code data
        if ($this->documentSignature && $this->documentSignature->qr_code_path) {
            $qrCodeFullPath = storage_path('app/public/' . $this->documentSignature->qr_code_path);

            if (file_exists($qrCodeFullPath)) {
                // Encode QR code as base64 for embedding in email
                $this->qrCodeBase64 = base64_encode(file_get_contents($qrCodeFullPath));
                $this->qrCodeUrl = Storage::disk('public')->path($this->documentSignature->qr_code_path);
            }
        }

        // Prepare verification URL
        if ($this->documentSignature && $this->documentSignature->verification_token) {
            $this->verificationUrl = route('signature.verify', $this->documentSignature->verification_token);
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $documentName = $this->approvalRequest->document_name;

        return new Envelope(
            subject: "✅ Dokumen Ditandatangani - {$documentName}",
            tags: ['digital-signature', 'document-signed'],
            // metadata: [
            //     'approval_request_id' => $this->approvalRequest->id,
            //     'document_signature_id' => $this->documentSignature->id ?? null,
            // ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval_request_signed',
            with: [
                'approvalRequest' => $this->approvalRequest,
                'documentSignature' => $this->documentSignature,
                'qrCodeBase64' => $this->qrCodeBase64,
                'qrCodeUrl' => $this->qrCodeUrl,
                'verificationUrl' => $this->verificationUrl,
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
        $attachments = [];

        // Attach signed PDF document
        if ($this->documentSignature && $this->documentSignature->final_pdf_path) {
            $signedPdfPath = storage_path('app/public/' . $this->documentSignature->final_pdf_path);

            if (file_exists($signedPdfPath)) {
                $documentName = $this->sanitizeFileName($this->approvalRequest->document_name);

                $attachments[] = Attachment::fromPath($signedPdfPath)
                    ->as("Signed_{$documentName}.pdf")
                    ->withMime('application/pdf');
            }
        }

        // Attach QR Code as separate file
        if ($this->documentSignature && $this->documentSignature->qr_code_path) {
            $qrCodePath = storage_path('app/public/' . $this->documentSignature->qr_code_path);

            if (file_exists($qrCodePath)) {
                $documentName = $this->sanitizeFileName($this->approvalRequest->document_name);

                $attachments[] = Attachment::fromPath($qrCodePath)
                    ->as("QRCode_{$documentName}.png")
                    ->withMime('image/png');
            }
        }

        return $attachments;
    }

    /**
     * Sanitize filename for safe file naming
     */
    private function sanitizeFileName($filename)
    {
        // Remove file extension if exists
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        // Replace special characters with underscore
        $filename = preg_replace('/[^A-Za-z0-9\-_]/', '_', $filename);

        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Limit length
        $filename = substr($filename, 0, 50);

        return trim($filename, '_');
    }
}
