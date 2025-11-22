<?php

namespace App\Services;

use TCPDF;
use Exception;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * PDFSignatureService
 *
 * REFACTORED: Service untuk embedding QR code ke dalam PDF
 * Menggunakan TCPDF library untuk manipulasi PDF
 */
class PDFSignatureService
{
    /**
     * Temporary files to clean up
     */
    private array $tempFilesToClean = [];

    /**
     * REFACTORED: Embed QR code into PDF document
     * No signature template - only QR code at user-defined position
     *
     * @param string $originalPdfPath - Absolute path to original PDF file
     * @param string $qrCodePath - Absolute path to QR code image
     * @param array $qrPositioningData - QR position from user drag & drop
     * @param DocumentSignature $documentSignature - For metadata
     * @return string - Path to final PDF with embedded QR (storage path)
     * @throws Exception
     */
    //! DIPAKAI DI CONTROLLER DIGITALSIGNATURE PROCESSDOCUMENTSIGNING METHOD
    public function embedQRCodeIntoPDF(
        string $originalPdfPath,
        string $qrCodePath,
        array $qrPositioningData,
        DocumentSignature $documentSignature
    ): string {
        try {
            Log::info('Starting QR code embedding into PDF', [
                'original_pdf' => $originalPdfPath,
                'qr_code_path' => $qrCodePath,
                'qr_positioning_data' => $qrPositioningData,
                'document_signature_id' => $documentSignature->id
            ]);

            // Validate files exist
            if (!file_exists($originalPdfPath)) {
                throw new Exception("Original PDF file not found: {$originalPdfPath}");
            }

            if (!file_exists($qrCodePath)) {
                throw new Exception("QR code image not found: {$qrCodePath}");
            }

            // Parse QR positioning data from user drag & drop
            $page = $qrPositioningData['page'] ?? 1;
            $position = $qrPositioningData['position'] ?? ['x' => 0, 'y' => 0];
            $size = $qrPositioningData['size'] ?? ['width' => 50, 'height' => 50];
            $canvasDimensions = $qrPositioningData['canvas_dimensions'] ?? null;

            // Initialize FPDI (extends TCPDF with PDF import capability)
            $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('UMT Informatika • Digital Signature System');
            $pdf->SetAuthor($documentSignature->signer->name ?? 'UMT Informatika');
            $pdf->SetTitle('Signed Document ID #' . $documentSignature->id);
            $pdf->SetSubject('Digitally Signed Document with QR Code');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set auto page breaks
            $pdf->SetAutoPageBreak(false, 0);

            // Detect PDF version and convert if needed
            $pdfVersion = $this->detectPdfVersion($originalPdfPath);
            $pdfToUse = $originalPdfPath; // Default: use original

            if (version_compare($pdfVersion, '1.4', '>')) {
                // PDF is 1.5+ → Need conversion
                Log::info('PDF version requires conversion', [
                    'version' => $pdfVersion,
                    'converting_from' => $pdfVersion,
                    'converting_to' => '1.4'
                ]);

                try {
                    // Convert PDF to 1.4
                    $pdfToUse = $this->convertPdfTo14($originalPdfPath);

                    // Track for cleanup
                    $this->tempFilesToClean[] = $pdfToUse;

                    Log::info('Using converted PDF for FPDI', [
                        'converted_path' => $pdfToUse
                    ]);

                } catch (Exception $e) {
                    Log::error('PDF conversion failed, will try original', [
                        'error' => $e->getMessage()
                    ]);

                    // Fallback: try original (might still fail)
                    $pdfToUse = $originalPdfPath;
                }
            } else {
                Log::info('PDF version compatible, no conversion needed', [
                    'version' => $pdfVersion
                ]);
            }

            // Load existing PDF (original or converted)
            $pageCount = $pdf->setSourceFile($pdfToUse);

            Log::info("PDF loaded successfully", [
                'total_pages' => $pageCount,
                'target_page' => $page,
                'pdf_used' => basename($pdfToUse)
            ]);

            // Import all pages from original PDF
            for ($i = 1; $i <= $pageCount; $i++) {
                // Import page
                $templateIdx = $pdf->importPage($i);

                // Get page dimensions
                $pageSize = $pdf->getTemplateSize($templateIdx);

                // Add a new page with same orientation and size
                $orientation = ($pageSize['width'] > $pageSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$pageSize['width'], $pageSize['height']]);

                // Use the imported page
                $pdf->useTemplate($templateIdx);

                // If this is the target page, add QR code at user-defined position
                if ($i == $page) {
                    $this->addQRCodeToPage(
                        $pdf,
                        $qrCodePath,
                        $position,
                        $size,
                        $pageSize,
                        $canvasDimensions
                    );
                }
            }

            // Get original filename
            $originalFileName = basename($originalPdfPath);

            // Sign file name with original name
            $signedFileName = 'signed_' . $originalFileName;

            // Generate filename for signed PDF
            // $signedFileName = 'signed_' . now()->format('YmdHis') . '_' . $documentSignature->id . '.pdf';
            $signedPdfStoragePath = 'signed-documents/' . $signedFileName;
            // $signedPdfAbsolutePath = Storage::path($signedPdfStoragePath);
            $signedPdfAbsolutePath = Storage::disk('public')->path($signedPdfStoragePath);

            // Ensure directory exists
            $directory = dirname($signedPdfAbsolutePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Output PDF to file
            $pdf->Output($signedPdfAbsolutePath, 'F');

            Log::info('QR code embedded into PDF successfully', [
                'output_path' => $signedPdfStoragePath,
                'file_size' => filesize($signedPdfAbsolutePath)
            ]);

            // Clean up temporary converted files
            $this->cleanupTempFiles();

            return $signedPdfStoragePath;

        } catch (Exception $e) {
            Log::error('QR code embedding failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'original_pdf' => $originalPdfPath,
                'qr_code_path' => $qrCodePath
            ]);

            // Clean up temp files even on error
            $this->cleanupTempFiles();

            throw new Exception('Failed to embed QR code into PDF: ' . $e->getMessage());
        }
    }

    /**
     * REFACTORED: Add QR code to PDF page at user-defined position
     *
     * @param Fpdi $pdf
     * @param string $qrCodePath - Absolute path to QR code image
     * @param array $position - ['x' => pixel, 'y' => pixel] from user drag & drop
     * @param array $size - ['width' => pixel, 'height' => pixel] from user drag & drop
     * @param array $pageSize - ['width' => mm, 'height' => mm]
     * @param array|null $canvasDimensions - ['width' => pixel, 'height' => pixel]
     * @return void
     */
    //! DIPAKAI DI EMBEDQRCODEINTOPDF METHOD
    private function addQRCodeToPage(
        TCPDF $pdf,
        string $qrCodePath,
        array $position,
        array $size,
        array $pageSize,
        ?array $canvasDimensions
    ): void {
        try {
            // Convert pixel coordinates to PDF points (mm)
            // Frontend uses canvas pixels, PDF uses millimeters

            // If canvas dimensions provided, use them for scaling
            if ($canvasDimensions) {
                $scaleX = $pageSize['width'] / $canvasDimensions['width'];
                $scaleY = $pageSize['height'] / $canvasDimensions['height'];
            } else {
                // Default: assume 96 DPI (1 mm = 3.7795 pixels)
                $pixelToMm = 0.2645833333; // 1 pixel = 0.2645833333 mm
                $scaleX = $pixelToMm;
                $scaleY = $pixelToMm;
            }

            // Calculate position and size in mm
            $x = $position['x'] * $scaleX;
            $y = $position['y'] * $scaleY;
            $width = $size['width'] * $scaleX;
            $height = $size['height'] * $scaleY;

            Log::info('Adding QR code to PDF at user-defined position', [
                'original_position_px' => $position,
                'original_size_px' => $size,
                'converted_position_mm' => ['x' => $x, 'y' => $y],
                'converted_size_mm' => ['width' => $width, 'height' => $height],
                'page_size_mm' => $pageSize
            ]);

            // Add QR code image at user's position
            $pdf->Image(
                $qrCodePath,
                $x,
                $y,
                $width,
                $height,
                '',
                '',
                '',
                false,
                300, // DPI
                '',
                false,
                false,
                0,
                false,
                false,
                false
            );

        } catch (Exception $e) {
            Log::error('Failed to add QR code to PDF', [
                'error' => $e->getMessage(),
                'qr_path' => $qrCodePath
            ]);
            throw $e;
        }
    }

    /**
     * Generate QR code image from verification URL
     *
     * @param string $verificationUrl
     * @param string $documentSignatureId
     * @return string|null - Absolute path to QR code image, or null on failure
     */
    public function generateQRCodeImage(string $verificationUrl, string $documentSignatureId): ?string
    {
        try {
            // Use SimpleSoftwareIO/simple-qrcode if available
            // If not, return null and skip QR code
            if (!class_exists('\SimpleSoftwareIO\QrCode\Facades\QrCode')) {
                Log::warning('QR Code library not available, skipping QR generation');
                return null;
            }

            $qrCodePath = storage_path('app/temp/qr_' . $documentSignatureId . '.png');

            // Ensure temp directory exists
            $tempDir = dirname($qrCodePath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Generate QR code
            \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                ->size(500)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($verificationUrl, $qrCodePath);

            Log::info('QR code generated', [
                'path' => $qrCodePath,
                'url' => $verificationUrl
            ]);

            return $qrCodePath;
        } catch (Exception $e) {
            Log::error('Failed to generate QR code', [
                'error' => $e->getMessage(),
                'url' => $verificationUrl
            ]);
            return null;
        }
    }

    /**
     * Clean up temporary files (QR codes and converted PDFs)
     *
     * @param string|null $qrCodePath
     * @return void
     */
    //! DIPAKAI DI EMBEDQRCODEINTOPDF METHOD
    public function cleanupTempFiles(?string $qrCodePath = null): void
    {
        // Clean up QR code if provided
        if ($qrCodePath && file_exists($qrCodePath)) {
            try {
                unlink($qrCodePath);
                Log::info('Temporary QR code file deleted', ['path' => $qrCodePath]);
            } catch (Exception $e) {
                Log::warning('Failed to delete temporary QR code', [
                    'path' => $qrCodePath,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Clean up converted PDF files
        foreach ($this->tempFilesToClean as $tempFile) {
            if (file_exists($tempFile)) {
                try {
                    unlink($tempFile);
                    Log::info('Temporary converted PDF deleted', ['path' => $tempFile]);
                } catch (Exception $e) {
                    Log::warning('Failed to delete temporary file', [
                        'path' => $tempFile,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        // Clear the tracking array
        $this->tempFilesToClean = [];
    }

    /**
     * Detect PDF version from file header
     *
     * @param string $pdfPath - Absolute path to PDF file
     * @return string - PDF version (e.g., '1.4', '1.5', etc.)
     * @throws Exception
     */
    //! DIPAKAI DI EMBEDQRCODEINTOPDF METHOD
    private function detectPdfVersion(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            throw new Exception("PDF file not found: {$pdfPath}");
        }

        // Read first 1024 bytes of PDF
        $handle = fopen($pdfPath, 'rb');
        $header = fread($handle, 1024);
        fclose($handle);

        // Look for PDF version in header
        // Pattern: %PDF-1.4 or %PDF-1.5 or %PDF-1.7, etc.
        if (preg_match('/%PDF-(\d\.\d)/', $header, $matches)) {
            $version = $matches[1];

            Log::info('PDF version detected',[
                'path' => basename($pdfPath),
                'version' => $version
            ]);

            return $version;
        }

        // Default to 1.4 if cannot detect
        Log::warning('Cannot detect PDF version, assuming 1.4', [
            'path' => $pdfPath
        ]);

        return '1.4';
    }

    /**
     * Convert PDF to version 1.4 using Ghostscript
     *
     * @param string $inputPath - Absolute path to input PDF
     * @return string - Absolute path to converted PDF (temp file)
     * @throws Exception
     */
    //! DIPAKAI DI EMBEDQRCODEINTOPDF METHOD
    private function convertPdfTo14(string $inputPath): string
    {
        try {
            // Generate temp file path
            $outputPath = storage_path('app/temp/converted_' . uniqid() . '.pdf');

            // Ensure temp directory exists
            $tempDir = dirname($outputPath);
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Build Ghostscript command
            $command = sprintf(
                'gs -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 ' .
                '-dPDFSETTINGS=/prepress -dNOPAUSE -dQUIET -dBATCH ' .
                '-sOutputFile=%s %s 2>&1',
                escapeshellarg($outputPath),
                escapeshellarg($inputPath)
            );

            Log::info('Converting PDF to version 1.4', [
                'input' => basename($inputPath),
                'output' => basename($outputPath),
                'command' => $command
            ]);

            // Execute Ghostscript
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $errorMsg = implode("\n", $output);
                Log::error('Ghostscript conversion failed', [
                    'return_code' => $returnCode,
                    'output' => $errorMsg,
                    'input' => $inputPath
                ]);

                throw new Exception('PDF conversion failed: ' . $errorMsg);
            }

            // Verify output file created
            if (!file_exists($outputPath) || filesize($outputPath) === 0) {
                throw new Exception('Converted PDF file not created or is empty');
            }

            Log::info('PDF converted successfully', [
                'input_size' => filesize($inputPath),
                'output_size' => filesize($outputPath),
                'output_path' => $outputPath
            ]);

            return $outputPath;

        } catch (Exception $e) {
            Log::error('PDF conversion error', [
                'error' => $e->getMessage(),
                'input' => $inputPath
            ]);

            throw $e;
        }
    }

    /**
     * Check if Ghostscript is available on the system
     *
     * @return bool
     */
    public function isGhostscriptAvailable(): bool
    {
        exec('gs --version 2>&1', $output, $returnCode);

        $available = ($returnCode === 0);

        if ($available) {
            Log::info('Ghostscript available', [
                'version' => $output[0] ?? 'unknown'
            ]);
        } else {
            Log::warning('Ghostscript NOT available', [
                'note' => 'PDF 1.5+ files may fail to process'
            ]);
        }

        return $available;
    }

    /**
     * ✅ ONE-PASS SOLUTION: Embed QR code + Sign PDF in ONE operation
     *
     * TCPDF setSignature() MUST be called DURING PDF creation, not after.
     * This method combines QR embedding and PDF signing into a single operation.
     *
     * @param string $originalPdfPath - Absolute path to original PDF
     * @param string $qrCodePath - Absolute path to QR code image
     * @param array $qrPositioningData - QR position from user drag & drop
     * @param DocumentSignature $documentSignature - For metadata
     * @param array $signingOptions - Certificate, key, signer info
     * @return string - Path to final signed PDF (storage path)
     * @throws Exception
     */
    public function embedQRCodeAndSignPDF(
        string $originalPdfPath,
        string $qrCodePath,
        array $qrPositioningData,
        DocumentSignature $documentSignature,
        array $signingOptions
    ): string {
        // Track temp files for cleanup
        $certTempFile = null;
        $keyTempFile = null;

        try {
            Log::info('Starting ONE-PASS QR embedding + PDF signing', [
                'original_pdf' => $originalPdfPath,
                'qr_code_path' => $qrCodePath,
                'signer' => $signingOptions['signer_name'] ?? 'Unknown',
                'document_signature_id' => $documentSignature->id
            ]);

            // ============================================
            // PHASE 1: VALIDATE FILES
            // ============================================

            if (!file_exists($originalPdfPath)) {
                throw new Exception("Original PDF file not found: {$originalPdfPath}");
            }

            if (!file_exists($qrCodePath)) {
                throw new Exception("QR code image not found: {$qrCodePath}");
            }

            // Validate signing options
            if (empty($signingOptions['certificate_pem'])) {
                throw new Exception("Certificate PEM is required for signing");
            }

            if (empty($signingOptions['private_key_pem'])) {
                throw new Exception("Private key PEM is required for signing");
            }

            // ============================================
            // PHASE 2: PREPARE CERTIFICATE FILES
            // ============================================

            // TCPDF setSignature() requires ACTUAL FILES, not strings
            $certTempFile = tempnam(sys_get_temp_dir(), 'cert_') . '.pem';
            $keyTempFile = tempnam(sys_get_temp_dir(), 'key_') . '.pem';

            file_put_contents($certTempFile, $signingOptions['certificate_pem']);
            file_put_contents($keyTempFile, $signingOptions['private_key_pem']);

            Log::info('Certificate files created', [
                'cert_file' => basename($certTempFile),
                'key_file' => basename($keyTempFile)
            ]);

            // ============================================
            // PHASE 3: INITIALIZE FPDI FOR PDF CREATION
            // ============================================

            // Parse QR positioning data
            $page = $qrPositioningData['page'] ?? 1;
            $position = $qrPositioningData['position'] ?? ['x' => 0, 'y' => 0];
            $size = $qrPositioningData['size'] ?? ['width' => 50, 'height' => 50];
            $canvasDimensions = $qrPositioningData['canvas_dimensions'] ?? null;

            // Initialize FPDI (extends TCPDF with PDF import capability)
            $pdf = new Fpdi('P', 'mm', 'A4', true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('UMT Informatika Digital Signature System');
            $pdf->SetAuthor($signingOptions['signer_name'] ?? 'UMT Informatika');
            $pdf->SetTitle('Digitally Signed Document ID #' . $documentSignature->signature_id);
            $pdf->SetSubject('Digitally Signed Document with QR Code');

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetAutoPageBreak(false, 0);

            // ============================================
            // PHASE 4: DETECT PDF VERSION & CONVERT IF NEEDED
            // ============================================

            $pdfVersion = $this->detectPdfVersion($originalPdfPath);
            $pdfToUse = $originalPdfPath;

            if (version_compare($pdfVersion, '1.4', '>')) {
                Log::info('PDF version requires conversion', [
                    'version' => $pdfVersion,
                    'converting_to' => '1.4'
                ]);

                try {
                    $pdfToUse = $this->convertPdfTo14($originalPdfPath);
                    $this->tempFilesToClean[] = $pdfToUse;

                    Log::info('Using converted PDF', [
                        'converted_path' => $pdfToUse
                    ]);
                } catch (Exception $e) {
                    Log::error('PDF conversion failed, using original', [
                        'error' => $e->getMessage()
                    ]);
                    $pdfToUse = $originalPdfPath;
                }
            }

            // ============================================
            // PHASE 5: IMPORT ORIGINAL PDF PAGES
            // ============================================

            $pageCount = $pdf->setSourceFile($pdfToUse);

            Log::info('PDF loaded successfully', [
                'total_pages' => $pageCount,
                'target_page_for_qr' => $page
            ]);

            // Variable to store QR code coordinates in mm (for signature appearance positioning)
            $qrCoordinatesMm = null;

            // Import all pages
            for ($i = 1; $i <= $pageCount; $i++) {
                $templateIdx = $pdf->importPage($i);
                $pageSize = $pdf->getTemplateSize($templateIdx);

                $orientation = ($pageSize['width'] > $pageSize['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$pageSize['width'], $pageSize['height']]);
                $pdf->useTemplate($templateIdx);

                // If this is the target page, add QR code
                if ($i == $page) {
                    // Calculate QR coordinates in mm (same logic as addQRCodeToPage)
                    if ($canvasDimensions) {
                        $scaleX = $pageSize['width'] / $canvasDimensions['width'];
                        $scaleY = $pageSize['height'] / $canvasDimensions['height'];
                    } else {
                        $pixelToMm = 0.2645833333;
                        $scaleX = $pixelToMm;
                        $scaleY = $pixelToMm;
                    }

                    $qrCoordinatesMm = [
                        'x' => $position['x'] * $scaleX,
                        'y' => $position['y'] * $scaleY,
                        'width' => $size['width'] * $scaleX,
                        'height' => $size['height'] * $scaleY,
                        'page' => $i
                    ];

                    $this->addQRCodeToPage(
                        $pdf,
                        $qrCodePath,
                        $position,
                        $size,
                        $pageSize,
                        $canvasDimensions
                    );
                }
            }

            // ============================================
            // PHASE 6: SET DIGITAL SIGNATURE (CRITICAL!)
            // ============================================

            // Prepare signature info for Adobe Reader
            $signatureInfo = [
                'Name' => $signingOptions['signer_name'] ?? 'Digital Signer',
                'Location' => $signingOptions['location'] ?? 'Universitas Muhammadiyah Tangerang',
                'Reason' => $signingOptions['reason'] ?? 'Document Approval',
                'ContactInfo' => $signingOptions['contact_info'] ?? 'informatika@umt.ac.id'
            ];

            Log::info('Setting digital signature', [
                'signature_info' => $signatureInfo
            ]);

            // ✅ CALL setSignature() - This embeds the signature into PDF
            // IMPORTANT: Must be called BEFORE Output()
            $pdf->setSignature(
                'file://' . $certTempFile,     // Certificate file path
                'file://' . $keyTempFile,      // Private key file path
                '',                             // Password (empty = no password)
                '',                             // Extra certificates (empty = none)
                2,                              // Signature type: 2 = approval signature
                $signatureInfo                  // Signature metadata for Adobe Reader
            );

            // ============================================
            // PHASE 7: SET SIGNATURE APPEARANCE (NEXT TO QR CODE)
            // ============================================

            // Calculate signature box position (to the right of QR code)
            if ($qrCoordinatesMm) {
                // Set page to where QR code is placed
                $pdf->setPage($qrCoordinatesMm['page']);

                // Position signature box to the right of QR code
                // $signatureX = $qrCoordinatesMm['x'] + $qrCoordinatesMm['width']; // 0mm gap

                // Position signature centered horizontally with QR code
                $signatureX = $qrCoordinatesMm['x'] + ($qrCoordinatesMm['width'] - 60) / 2;

                $signatureY = $qrCoordinatesMm['y'];
                $signatureWidth = 60;  // 60mm width for signature box
                $signatureHeight = $qrCoordinatesMm['height']; // Match QR code height

                Log::info('Calculated signature appearance position', [
                    'qr_position' => [
                        'x' => $qrCoordinatesMm['x'],
                        'y' => $qrCoordinatesMm['y'],
                        'width' => $qrCoordinatesMm['width'],
                        'height' => $qrCoordinatesMm['height']
                    ],
                    'signature_position' => [
                        'x' => $signatureX,
                        'y' => $signatureY,
                        'width' => $signatureWidth,
                        'height' => $signatureHeight
                    ],
                    'gap_mm' => 0
                ]);
            } else {
                // Fallback: default position if QR coordinates not available
                $pdf->setPage($pageCount);
                $signatureX = 10;
                $signatureY = 10;
                $signatureWidth = 80;
                $signatureHeight = 30;

                Log::warning('QR coordinates not available, using default signature position', [
                    'default_position' => ['x' => $signatureX, 'y' => $signatureY],
                    'default_size' => ['width' => $signatureWidth, 'height' => $signatureHeight]
                ]);
            }

            // Define signature appearance area (x, y, w, h in mm)
            $pdf->setSignatureAppearance(
                $signatureX,
                $signatureY,
                $signatureWidth,
                $signatureHeight,
                -1,
                $documentSignature->approvalRequest->approver->name ?? 'Digital Signer'
            );

            Log::info('Signature appearance set next to QR code', [
                'position' => ['x' => $signatureX, 'y' => $signatureY],
                'size' => ['width' => $signatureWidth, 'height' => $signatureHeight],
                'signer_name' => $documentSignature->approvalRequest->approver->name ?? 'Digital Signer'
            ]);

            // ============================================
            // PHASE 8: OUTPUT SIGNED PDF
            // ============================================

            // Get original filename
            $originalFileName = basename($originalPdfPath);
            $signedFileName = 'signed_' . $originalFileName;

            // Generate storage path
            $signedPdfStoragePath = 'signed-documents/' . $signedFileName;
            $signedPdfAbsolutePath = Storage::disk('public')->path($signedPdfStoragePath);

            // Ensure directory exists
            $directory = dirname($signedPdfAbsolutePath);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            // Output to file - Signature is embedded at this moment!
            $pdf->Output($signedPdfAbsolutePath, 'F');

            Log::info('PDF signed successfully with ONE-PASS method', [
                'output_path' => $signedPdfStoragePath,
                'absolute_path' => $signedPdfAbsolutePath,
                'file_size' => filesize($signedPdfAbsolutePath),
                'has_signature' => true,
                'adobe_reader_compatible' => true
            ]);

            // Clean up temp files
            $this->cleanupTempFiles();

            return $signedPdfStoragePath;

        } catch (Exception $e) {
            Log::error('ONE-PASS signing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'original_pdf' => $originalPdfPath,
                'qr_code_path' => $qrCodePath
            ]);

            // Clean up temp files
            $this->cleanupTempFiles();

            throw new Exception('Failed to sign PDF with ONE-PASS method: ' . $e->getMessage());

        } finally {
            // GUARANTEED CLEANUP: Remove certificate temp files
            if ($certTempFile !== null && file_exists($certTempFile)) {
                @unlink($certTempFile);
                Log::debug('Cleaned up certificate temp file');
            }

            if ($keyTempFile !== null && file_exists($keyTempFile)) {
                @unlink($keyTempFile);
                Log::debug('Cleaned up private key temp file');
            }
        }
    }
}
