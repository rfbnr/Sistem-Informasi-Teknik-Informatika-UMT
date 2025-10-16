<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use Illuminate\Http\Request;
use App\Models\SignatureRequest;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Log;
use App\Models\BlockchainTransaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SignatureValidationController extends Controller
{
    protected $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Show the signature validation page
     */
    public function index()
    {
        return view('validation.index');
    }

    /**
     * Validate signature by document hash
     */
    public function validateByHash(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_hash' => 'required|string|min:64|max:64'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format hash dokumen tidak valid'
            ], 400);
        }

        try {
            $documentHash = $request->document_hash;

            // Find document by hash
            $document = Document::where('file_hash', $documentHash)->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen dengan hash tersebut tidak ditemukan'
                ], 404);
            }

            // Get signature requests for this document
            $signatureRequests = SignatureRequest::where('document_id', $document->id)
                ->with(['signatures.signer', 'requester'])
                ->get();

            // Validate blockchain integrity
            $blockchainValidation = $this->validateBlockchainIntegrity($document);

            // Validate file integrity
            $fileValidation = $this->validateFileIntegrity($document);

            // Validate signatures
            $signatureValidation = $this->validateSignatures($signatureRequests);

            $result = [
                'success' => true,
                'document' => [
                    'title' => $document->title,
                    'file_name' => $document->file_name,
                    'file_hash' => $document->file_hash,
                    'created_at' => $document->created_at->format('d M Y H:i:s'),
                    'file_size' => $document->file_size
                ],
                'validation' => [
                    'file_integrity' => $fileValidation,
                    'blockchain_integrity' => $blockchainValidation,
                    'signatures' => $signatureValidation,
                    'overall_status' => $this->calculateOverallStatus($fileValidation, $blockchainValidation, $signatureValidation)
                ],
                'signature_requests' => $signatureRequests->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'title' => $request->title,
                        'status' => $request->status,
                        'requester' => $request->requester->name,
                        'created_at' => $request->created_at->format('d M Y H:i:s'),
                        'signatures' => $request->signatures->map(function ($signature) {
                            return [
                                'signer_name' => $signature->signer->name,
                                'signer_role' => $signature->signer_role,
                                'status' => $signature->status,
                                'signed_at' => $signature->signed_at ? $signature->signed_at->format('d M Y H:i:s') : null,
                                'location' => $signature->location,
                                'ip_address' => $signature->ip_address,
                                'signature_hash' => $signature->signature_hash
                            ];
                        })
                    ];
                })
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Signature validation error', [
                'hash' => $request->document_hash,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat validasi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate signature by QR code
     */
    public function validateByQR(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code tidak valid'
            ], 400);
        }

        try {
            // Decode QR code data
            $qrData = base64_decode($request->qr_code);
            $data = json_decode($qrData, true);

            if (!$data || !isset($data['signature_id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Format QR Code tidak valid'
                ], 400);
            }

            // Find signature
            $signature = Signature::with(['signatureRequest.document', 'signer'])
                ->find($data['signature_id']);

            if (!$signature) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tanda tangan tidak ditemukan'
                ], 404);
            }

            // Validate QR integrity
            $qrValidation = $this->validateQRIntegrity($signature, $data);

            // Validate signature integrity
            $signatureValidation = $this->validateSingleSignature($signature);

            $result = [
                'success' => true,
                'signature' => [
                    'id' => $signature->id,
                    'signer_name' => $signature->signer->name,
                    'signer_role' => $signature->signer_role,
                    'status' => $signature->status,
                    'signed_at' => $signature->signed_at ? $signature->signed_at->format('d M Y H:i:s') : null,
                    'location' => $signature->location,
                    'ip_address' => $signature->ip_address
                ],
                'document' => [
                    'title' => $signature->signatureRequest->document->title,
                    'file_name' => $signature->signatureRequest->document->file_name,
                    'file_hash' => $signature->signatureRequest->document->file_hash
                ],
                'validation' => [
                    'qr_integrity' => $qrValidation,
                    'signature_integrity' => $signatureValidation,
                    'overall_status' => $qrValidation['valid'] && $signatureValidation['valid'] ? 'valid' : 'invalid'
                ]
            ];

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('QR validation error', [
                'qr_code' => $request->qr_code,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat validasi QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate signature by file upload
     */
    public function validateByFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'File tidak valid atau ukuran terlalu besar (max 10MB)'
            ], 400);
        }

        try {
            $file = $request->file('file');

            // Calculate file hash
            $fileHash = hash_file('sha256', $file->getPathname());

            // Find document by hash
            $document = Document::where('file_hash', $fileHash)->first();

            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'File ini tidak ditemukan dalam sistem atau belum pernah ditandatangani'
                ], 404);
            }

            // Validate using hash method
            $request->merge(['document_hash' => $fileHash]);
            return $this->validateByHash($request);

        } catch (\Exception $e) {
            Log::error('File validation error', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat validasi file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate validation report
     */
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_hash' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Hash dokumen diperlukan'
            ], 400);
        }

        try {
            // Get validation data
            $validationData = $this->validateByHash($request)->getData(true);

            if (!$validationData['success']) {
                return response()->json($validationData);
            }

            // Generate PDF report
            $pdf = $this->generateValidationPDF($validationData);

            return response($pdf)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="validation-report-' . substr($request->document_hash, 0, 8) . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Report generation error', [
                'hash' => $request->document_hash,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate blockchain integrity
     */
    private function validateBlockchainIntegrity(Document $document)
    {
        try {
            $transactions = BlockchainTransaction::where('document_id', $document->id)
                ->where('status', 'confirmed')
                ->get();

            if ($transactions->isEmpty()) {
                return [
                    'valid' => false,
                    'message' => 'Tidak ada transaksi blockchain yang terkonfirmasi',
                    'details' => []
                ];
            }

            $validTransactions = 0;
            $details = [];

            foreach ($transactions as $transaction) {
                $isValid = $this->blockchainService->verifyTransaction($transaction->transaction_hash);

                if ($isValid) {
                    $validTransactions++;
                }

                $details[] = [
                    'transaction_hash' => $transaction->transaction_hash,
                    'type' => $transaction->transaction_type,
                    'valid' => $isValid,
                    'block_number' => $transaction->block_number,
                    'timestamp' => $transaction->created_at->format('d M Y H:i:s')
                ];
            }

            return [
                'valid' => $validTransactions === $transactions->count(),
                'message' => "$validTransactions dari {$transactions->count()} transaksi valid",
                'details' => $details
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Gagal memvalidasi blockchain: ' . $e->getMessage(),
                'details' => []
            ];
        }
    }

    /**
     * Validate file integrity
     */
    private function validateFileIntegrity(Document $document)
    {
        try {
            $filePath = storage_path('app/public/' . $document->file_path);

            if (!file_exists($filePath)) {
                return [
                    'valid' => false,
                    'message' => 'File tidak ditemukan di server',
                    'stored_hash' => $document->file_hash,
                    'current_hash' => null
                ];
            }

            $currentHash = hash_file('sha256', $filePath);

            return [
                'valid' => $currentHash === $document->file_hash,
                'message' => $currentHash === $document->file_hash ? 'File tidak dimodifikasi' : 'File telah dimodifikasi',
                'stored_hash' => $document->file_hash,
                'current_hash' => $currentHash
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Gagal memvalidasi file: ' . $e->getMessage(),
                'stored_hash' => $document->file_hash,
                'current_hash' => null
            ];
        }
    }

    /**
     * Validate signatures
     */
    private function validateSignatures($signatureRequests)
    {
        $totalSignatures = 0;
        $validSignatures = 0;
        $details = [];

        foreach ($signatureRequests as $request) {
            foreach ($request->signatures as $signature) {
                $totalSignatures++;
                $validation = $this->validateSingleSignature($signature);

                if ($validation['valid']) {
                    $validSignatures++;
                }

                $details[] = $validation;
            }
        }

        return [
            'valid' => $totalSignatures > 0 && $validSignatures === $totalSignatures,
            'message' => "$validSignatures dari $totalSignatures tanda tangan valid",
            'total_signatures' => $totalSignatures,
            'valid_signatures' => $validSignatures,
            'details' => $details
        ];
    }

    /**
     * Validate single signature
     */
    private function validateSingleSignature(Signature $signature)
    {
        try {
            $checks = [];

            // Check signature hash
            if ($signature->signature_hash) {
                $expectedHash = hash('sha256', $signature->signature_data . $signature->signer_id . $signature->signed_at);
                $checks['hash_valid'] = $signature->signature_hash === $expectedHash;
            } else {
                $checks['hash_valid'] = false;
            }

            // Check signature data exists
            $checks['data_exists'] = !empty($signature->signature_data);

            // Check timestamp is reasonable
            $checks['timestamp_valid'] = $signature->signed_at && $signature->signed_at->isPast();

            // Check if signature is not tampered
            $checks['not_tampered'] = $signature->status === 'signed' && $signature->signature_data;

            $allValid = collect($checks)->every(fn($check) => $check === true);

            return [
                'valid' => $allValid,
                'signature_id' => $signature->id,
                'signer_name' => $signature->signer->name,
                'signed_at' => $signature->signed_at ? $signature->signed_at->format('d M Y H:i:s') : null,
                'checks' => $checks,
                'message' => $allValid ? 'Tanda tangan valid' : 'Tanda tangan tidak valid'
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'signature_id' => $signature->id,
                'signer_name' => $signature->signer->name ?? 'Unknown',
                'message' => 'Error validating signature: ' . $e->getMessage(),
                'checks' => []
            ];
        }
    }

    /**
     * Validate QR integrity
     */
    private function validateQRIntegrity(Signature $signature, array $qrData)
    {
        try {
            $expectedData = [
                'signature_id' => $signature->id,
                'document_hash' => $signature->signatureRequest->document->file_hash,
                'signer_id' => $signature->signer_id,
                'signed_at' => $signature->signed_at ? $signature->signed_at->timestamp : null
            ];

            $dataMatch = true;
            $differences = [];

            foreach ($expectedData as $key => $expectedValue) {
                if (!isset($qrData[$key]) || $qrData[$key] !== $expectedValue) {
                    $dataMatch = false;
                    $differences[] = "Mismatch in $key";
                }
            }

            return [
                'valid' => $dataMatch,
                'message' => $dataMatch ? 'QR Code valid' : 'QR Code tidak sesuai dengan data tanda tangan',
                'differences' => $differences
            ];

        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error validating QR: ' . $e->getMessage(),
                'differences' => []
            ];
        }
    }

    /**
     * Calculate overall validation status
     */
    private function calculateOverallStatus($fileValidation, $blockchainValidation, $signatureValidation)
    {
        if ($fileValidation['valid'] && $blockchainValidation['valid'] && $signatureValidation['valid']) {
            return 'valid';
        } elseif (!$fileValidation['valid']) {
            return 'file_tampered';
        } elseif (!$blockchainValidation['valid']) {
            return 'blockchain_invalid';
        } elseif (!$signatureValidation['valid']) {
            return 'signature_invalid';
        } else {
            return 'invalid';
        }
    }

    /**
     * Generate validation PDF report
     */
    private function generateValidationPDF($validationData)
    {
        // This would typically use a PDF library like DomPDF or similar
        // For now, return a simple text report
        $report = "LAPORAN VALIDASI TANDA TANGAN DIGITAL\n";
        $report .= "=====================================\n\n";
        $report .= "Dokumen: " . $validationData['document']['title'] . "\n";
        $report .= "Hash: " . $validationData['document']['file_hash'] . "\n";
        $report .= "Status: " . strtoupper($validationData['validation']['overall_status']) . "\n\n";

        $report .= "Detail Validasi:\n";
        $report .= "- File Integrity: " . ($validationData['validation']['file_integrity']['valid'] ? 'VALID' : 'INVALID') . "\n";
        $report .= "- Blockchain Integrity: " . ($validationData['validation']['blockchain_integrity']['valid'] ? 'VALID' : 'INVALID') . "\n";
        $report .= "- Signatures: " . ($validationData['validation']['signatures']['valid'] ? 'VALID' : 'INVALID') . "\n";

        $report .= "\nDibuat pada: " . now()->format('d M Y H:i:s') . "\n";

        return $report;
    }
}