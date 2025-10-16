<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\SignatureRequest;
use App\Services\BlockchainService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class DocumentController extends Controller
{
    use AuthorizesRequests;

    protected $blockchainService;

    public function __construct(BlockchainService $blockchainService)
    {
        $this->blockchainService = $blockchainService;
    }

    /**
     * Display student's documents
     */
    public function index()
    {
        $documents = Document::where('user_id', Auth::id())
            ->with(['signatureRequests.signatures'])
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    /**
     * Show upload form
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Upload new document
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:academic_transcript,certificate,thesis,research_proposal,internship_report,other',
            'document' => 'required|file|mimes:pdf|max:25600', // 25MB max
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Store file
            $file = $request->file('document');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('documents', $fileName, 'public');

            // Generate file hash
            $fileHash = hash_file('sha256', $file->getRealPath());

            // Create document record
            $document = Document::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'category' => $request->category,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'file_hash' => $fileHash,
                'mime_type' => $file->getMimeType(),
                'status' => 'uploaded',
                'metadata' => [
                    'original_name' => $file->getClientOriginalName(),
                    'uploaded_at' => now()->toISOString(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            ]);

            // Store document hash on blockchain
            $this->blockchainService->storeDocumentHash($document);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Dokumen berhasil diunggah dan disimpan di blockchain!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal mengunggah dokumen: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show document details
     */
    public function show(Document $document)
    {
        $this->authorize('view', $document);

        $signatureRequests = $document->signatureRequests()
            ->with(['signatures.signer', 'signees'])
            ->latest()
            ->get();

        return view('documents.show', compact('document', 'signatureRequests'));
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        $this->authorize('view', $document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            abort(404, 'File not found');
        }

        // Verify document integrity
        if (!$document->verifyIntegrity()) {
            return redirect()->back()
                ->with('error', 'Document integrity check failed. File may have been tampered with.');
        }

        $filePath = storage_path('app/public/' . $document->file_path);
        $fileName = $document->metadata['original_name'] ?? 'document.pdf';

        return response()->download($filePath, $fileName);
    }

    /**
     * Create signature request
     */
    public function createSignatureRequest(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'workflow_type' => 'required|in:sequential,parallel,conditional',
            'deadline' => 'nullable|date|after:now',
            'priority' => 'required|in:low,medium,high,urgent',
            'signees' => 'required|array|min:1',
            'signees.*.user_id' => 'required|exists:users,id',
            'signees.*.role' => 'required|in:signer,reviewer,approver,witness,cc',
            'signees.*.order' => 'required|integer|min:1',
            'signees.*.required' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Create signature request
            $signatureRequest = SignatureRequest::create([
                'document_id' => $document->id,
                'requester_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'workflow_type' => $request->workflow_type,
                'deadline' => $request->deadline,
                'priority' => $request->priority,
                'status' => 'pending',
                'metadata' => [
                    'created_at' => now()->toISOString(),
                    'ip_address' => $request->ip()
                ]
            ]);

            // Add signees
            foreach ($request->signees as $signeeData) {
                $signatureRequest->signees()->attach($signeeData['user_id'], [
                    'role' => $signeeData['role'],
                    'order' => $signeeData['order'],
                    'required' => $signeeData['required'] ?? true,
                    'status' => 'pending'
                ]);
            }

            // Update document status
            $document->update(['status' => 'ready_for_signature']);

            // Send notifications to signees
            $this->notifySignees($signatureRequest);

            return redirect()->route('documents.show', $document)
                ->with('success', 'Permintaan tanda tangan berhasil dibuat!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat permintaan tanda tangan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verify document integrity
     */
    public function verifyIntegrity(Document $document)
    {
        $this->authorize('view', $document);

        $localIntegrity = $document->verifyIntegrity();
        $blockchainIntegrity = $this->blockchainService->verifyDocumentHash($document->file_hash);

        return response()->json([
            'document_id' => $document->id,
            'local_integrity' => $localIntegrity,
            'blockchain_integrity' => $blockchainIntegrity,
            'overall_status' => $localIntegrity && $blockchainIntegrity ? 'verified' : 'compromised',
            'file_hash' => $document->file_hash,
            'verified_at' => now()->toISOString()
        ]);
    }

    /**
     * Send notifications to signees
     */
    private function notifySignees(SignatureRequest $signatureRequest)
    {
        // Implementation for sending email notifications
        foreach ($signatureRequest->signees as $signee) {
            // Send email notification to each signee
            // Mail::to($signee->email)->queue(new SignatureRequestNotification($signatureRequest));
        }
    }
}