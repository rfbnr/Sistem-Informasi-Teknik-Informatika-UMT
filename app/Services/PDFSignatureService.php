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
}
