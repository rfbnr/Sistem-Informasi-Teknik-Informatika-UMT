<?php

namespace App\Http\Controllers;

use App\Models\Kaprodi;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\NewApprovalRequestNotification;
use App\Mail\ApprovalRequestSignedNotification;
use App\Mail\ApprovalRequestApprovedNotification;
use App\Mail\ApprovalRequestRejectedNotification;




class ApprovalRequestController extends Controller
{
    public function create()
    {
        return view('user.aproval');
    }


    public function upload(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->back()->with('error', 'Anda harus login terlebih dahulu untuk mengunggah dokumen.');
        }

        $validator = Validator::make($request->all(), [
            'document_name' => 'required|string',
            'document' => 'required|file|mimes:pdf|max:25600', // max file size 25MB
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $documentPath = $request->file('document')->store('documents', 'public');

        $approvalRequest = ApprovalRequest::create([
            'user_id' => Auth::id(),
            'document_name' => $request->input('document_name'),
            'document_path' => $documentPath,
            'notes' => $request->input('notes'),
        ]);

        // Mengambil email Kaprodi
        $kaprodiEmails = Kaprodi::pluck('email')->toArray();

        // Mengirim email notifikasi ke Kaprodi
        Mail::to($kaprodiEmails)->send(new \App\Mail\NewApprovalRequestNotification($approvalRequest));

        return redirect('mahasiswa/status')->with('success', 'File uploaded successfully!');
    }


    // Display all approval requests for Kaprodi
    public function index()
    {
        $approvalRequests = ApprovalRequest::all();
        return view('approval_requests.index', compact('approvalRequests'));
    }

    // Approve a request
    public function approve($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        $approvalRequest->update(['status' => 'approved']);

        // Enkripsi ID
        $encryptedId = Crypt::encryptString($approvalRequest->id);

        // Buat URL unduhan untuk dokumen yang ditandatangani dengan ID terenkripsi
        $downloadUrl = url('/terverfikasi/disetujui/' . $encryptedId);

        // Pastikan direktori `qrcodes` ada
        $qrCodeDirectory = storage_path('app/public/qrcodes');
        if (!File::exists($qrCodeDirectory)) {
            File::makeDirectory($qrCodeDirectory, 0755, true);
        }

        // Hasilkan QR code menggunakan endroid/qr-code dan simpan sebagai gambar
        $qrCode = new QrCode($downloadUrl);
        $writer = new PngWriter();
        $qrCodePath = 'qrcodes/' . $approvalRequest->id . '.png';
        $writer->write($qrCode)->saveToFile(storage_path('app/public/' . $qrCodePath));

        // URL gambar QR code
        $qrCodeUrl = Storage::url($qrCodePath);

        Mail::to($approvalRequest->user->email)->send(new ApprovalRequestApprovedNotification($approvalRequest,  $qrCodeUrl));

        return back()->with('success', 'Request approved successfully!');
    }

    // Reject a request
    public function reject($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);
        $approvalRequest->update(['status' => 'rejected']);


        // Send notification to student
        Mail::to($approvalRequest->user->email)->send(new ApprovalRequestRejectedNotification($approvalRequest));

        return back()->with('success', 'Request rejected successfully!');
    }

    // Display status of approval requests for the student
    public function status()
    {
        $approvalRequests = ApprovalRequest::where('user_id', Auth::id())->get();
        return view('approval_requests.status', compact('approvalRequests'));
    }


    // public function uploadSignedDocument(Request $request, $id)
    // {
    //     \Log::info('Request received:', $request->all());

    //     // Validate input
    //     $request->validate([
    //         'signed_document_path' => 'required|file|mimes:pdf|max:25000',
    //     ]);

    //     // Retrieve the approval request by ID
    //     $approvalRequest = ApprovalRequest::findOrFail($id);

    //     // Handle the file upload
    //     if ($request->hasFile('signed_document_path')) {
    //         $file = $request->file('signed_document_path');
    //         \Log::info('File received:', ['filename' => $file->getClientOriginalName()]);

    //         // Store the uploaded file in the 'signed_documents' directory in public storage
    //         $signedDocumentPath = $file->store('signed_documents', 'public');

    //         // Update the approval request with the file path and change the status to 'approved'
    //         $approvalRequest->update([
    //             'signed_document_path' => $signedDocumentPath,
    //             'status' => 'approved'
    //         ]);

    //         // Create the download URL for the signed document
    //         $downloadUrl = url('/approval-requests/download-signed/' . $approvalRequest->id);

    //         // Ensure the QR code directory exists
    //         $qrCodeDirectory = storage_path('app/public/qrcodes');
    //         if (!File::exists($qrCodeDirectory)) {
    //             File::makeDirectory($qrCodeDirectory, 0755, true);
    //         }

    //         // Generate the QR code for the download URL and save it as a PNG image
    //         $qrCode = new QrCode($downloadUrl);
    //         $writer = new PngWriter();
    //         $qrCodePath = 'qrcodes/' . $approvalRequest->id . '.png';
    //         $writer->write($qrCode)->saveToFile(storage_path('app/public/' . $qrCodePath));

    //         // Get the URL of the QR code image
    //         $qrCodeUrl = Storage::url($qrCodePath);

    //         // Send a notification email to the user with the QR code
    //         Mail::to($approvalRequest->user->email)->send(new ApprovalRequestSignedNotification($approvalRequest, $qrCodeUrl));

    //         return back()->with('success', 'Signed document uploaded and request approved successfully!');
    //     } else {
    //         return back()->withErrors(['signed_document_path' => 'File upload failed.']);
    //     }
    // }

    public function uploadSignedDocument(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'signed_document_path' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Temukan request approval yang sesuai
        $approvalRequest = ApprovalRequest::findOrFail($id);

        // Proses file upload
        if ($request->hasFile('signed_document_path')) {
            $file = $request->file('signed_document_path');
            $path = $file->store('signed_documents', 'public');

            \Log::info('Path dokumen yang di-upload: ' . $path);

            // Perbarui path dokumen yang telah ditandatangani di database
            $approvalRequest->signed_document_path = $path;
            $approvalRequest->status = 'approved';

            try {
                $approvalRequest->save();
                \Log::info('Database berhasil diperbarui untuk ID: ' . $id);
            } catch (\Exception $e) {
                \Log::error('Gagal memperbarui database untuk ID: ' . $id . '. Error: ' . $e->getMessage());
                return back()->withErrors(['error' => 'Gagal memperbarui database.']);
            }
        } else {
            return back()->withErrors(['signed_document_path' => 'File upload failed.']);
        }

        return response()->json(['message' => 'Document uploaded successfully.']);
    }





    public function downloadSignedDocument($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);

        // if ($approvalRequest->user_id !== Auth::guard('web')->id()) {
        //     abort(403, 'Unauthorized action.');
        // } else if ()

        $filePath = storage_path('app/' . $approvalRequest->signed_document_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $approvalRequest->document_name . '_signed.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    public function downloadDocument($id)
    {
        $approvalRequest = ApprovalRequest::findOrFail($id);

        // Pastikan hanya Kaprodi yang bisa melihat dokumen ini
        // if (!Auth::user()->isKaprodi()) {
        //     abort(403, 'Unauthorized action.');
        // }

        $filePath = storage_path('app/' . $approvalRequest->document_path);

        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        return response()->download($filePath, $approvalRequest->document_name . '.' . pathinfo($filePath, PATHINFO_EXTENSION));
    }

    public function showUploadForm()
    {
        $hasApprovalRequests = ApprovalRequest::where('user_id', Auth::id())->exists();
        return view('user.aproval', compact('hasApprovalRequests'));
    }
}
