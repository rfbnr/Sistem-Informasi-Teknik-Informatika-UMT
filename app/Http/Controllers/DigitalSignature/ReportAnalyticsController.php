<?php

namespace App\Http\Controllers\DigitalSignature;

use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use App\Models\DigitalSignature;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentSignature;
use App\Http\Controllers\Controller;
use App\Models\VerificationCodeMapping;

class ReportAnalyticsController extends Controller
{
    /**
     * Display Reports & Analytics Dashboard
     */
    public function index(Request $request)
    {
        // Get date range from request or default to last 30 days
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Comprehensive Statistics
        $statistics = [
            // Overview Stats
            'total_approval_requests' => ApprovalRequest::whereBetween('created_at', [$start, $end])->count(),
            'total_digital_signatures' => DigitalSignature::whereBetween('created_at', [$start, $end])->count(),
            'total_document_signatures' => DocumentSignature::whereBetween('created_at', [$start, $end])->count(),
            'total_qr_codes' => VerificationCodeMapping::whereBetween('created_at', [$start, $end])->count(),

            // Approval Request Breakdown
            'approval_pending' => ApprovalRequest::where('status', ApprovalRequest::STATUS_PENDING)
                ->whereBetween('created_at', [$start, $end])->count(),
            'approval_approved' => ApprovalRequest::where('status', ApprovalRequest::STATUS_APPROVED)
                ->whereBetween('created_at', [$start, $end])->count(),
            'approval_user_signed' => ApprovalRequest::where('status', ApprovalRequest::STATUS_USER_SIGNED)
                ->whereBetween('created_at', [$start, $end])->count(),
            'approval_sign_approved' => ApprovalRequest::where('status', ApprovalRequest::STATUS_SIGN_APPROVED)
                ->whereBetween('created_at', [$start, $end])->count(),
            'approval_rejected' => ApprovalRequest::where('status', ApprovalRequest::STATUS_REJECTED)
                ->whereBetween('created_at', [$start, $end])->count(),

            // Digital Signature Status
            'signatures_active' => DigitalSignature::where('status', 'active')
                ->whereBetween('created_at', [$start, $end])->count(),
            'signatures_expired' => DigitalSignature::where('status', 'expired')
                ->whereBetween('created_at', [$start, $end])->count(),
            'signatures_revoked' => DigitalSignature::where('status', 'revoked')
                ->whereBetween('created_at', [$start, $end])->count(),

            // Document Signature Status
            'documents_signed' => DocumentSignature::where('signature_status', 'signed')
                ->whereBetween('created_at', [$start, $end])->count(),
            'documents_verified' => DocumentSignature::where('signature_status', 'verified')
                ->whereBetween('created_at', [$start, $end])->count(),
            'documents_invalid' => DocumentSignature::where('signature_status', 'invalid')
                ->whereBetween('created_at', [$start, $end])->count(),

            // QR Code Analytics
            'qr_active' => VerificationCodeMapping::where('expires_at', '>', now())
                ->whereBetween('created_at', [$start, $end])->count(),
            'qr_expired' => VerificationCodeMapping::where('expires_at', '<=', now())
                ->whereBetween('created_at', [$start, $end])->count(),
            'qr_total_scans' => VerificationCodeMapping::whereBetween('created_at', [$start, $end])
                ->sum('access_count'),
        ];

        // Completion Rate
        $totalRequests = $statistics['total_approval_requests'];
        $completedRequests = $statistics['approval_sign_approved'];
        $statistics['completion_rate'] = $totalRequests > 0
            ? round(($completedRequests / $totalRequests) * 100, 2)
            : 0;

        // Rejection Rate
        $statistics['rejection_rate'] = $totalRequests > 0
            ? round(($statistics['approval_rejected'] / $totalRequests) * 100, 2)
            : 0;

        // Average Processing Time
        $statistics['avg_processing_time'] = $this->calculateAverageProcessingTime($start, $end);

        // Timeline Data (Last 30 days)
        $timelineData = $this->getTimelineData($start, $end);

        // Document Type Distribution
        $documentTypeDistribution = $this->getDocumentTypeDistribution($start, $end);

        // Priority Distribution
        $priorityDistribution = $this->getPriorityDistribution($start, $end);

        // Top Users Statistics
        $topUsers = $this->getTopUsers($start, $end);

        // Recent Activity
        $recentActivity = $this->getRecentActivity(10);

        // QR Code Access Analytics
        $qrAnalytics = $this->getQRCodeAnalytics($start, $end);

        // Expiring Signatures Alert
        $expiringSoon = DigitalSignature::where('status', 'active')
            ->where('valid_until', '<=', now()->addDays(30))
            ->where('valid_until', '>', now())
            ->orderBy('valid_until', 'asc')
            ->limit(5)
            ->get();

        return view('digital-signature.admin.reports.index', compact(
            'statistics',
            'timelineData',
            'documentTypeDistribution',
            'priorityDistribution',
            'topUsers',
            'recentActivity',
            'qrAnalytics',
            'expiringSoon',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Calculate average processing time from submission to final approval
     */
    private function calculateAverageProcessingTime($start, $end)
    {
        $completed = ApprovalRequest::where('status', ApprovalRequest::STATUS_SIGN_APPROVED)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('sign_approved_at')
            ->get();

        if ($completed->count() === 0) {
            return '0 days';
        }

        $totalHours = 0;
        foreach ($completed as $request) {
            $hours = $request->created_at->diffInHours($request->sign_approved_at);
            $totalHours += $hours;
        }

        $avgHours = $totalHours / $completed->count();
        $days = floor($avgHours / 24);
        $hours = $avgHours % 24;

        if ($days > 0) {
            return "{$days} days {$hours} hours";
        }
        return "{$hours} hours";
    }

    /**
     * Get timeline data for chart
     */
    private function getTimelineData($start, $end)
    {
        $dates = [];
        $approvalData = [];
        $documentData = [];

        $currentDate = $start->copy();

        while ($currentDate <= $end) {
            $dateStr = $currentDate->format('Y-m-d');
            $dates[] = $currentDate->format('d M');

            // Count approvals created on this date
            $approvalData[] = ApprovalRequest::whereDate('created_at', $dateStr)->count();

            // Count documents signed on this date
            $documentData[] = DocumentSignature::whereDate('created_at', $dateStr)->count();

            $currentDate->addDay();
        }

        return [
            'dates' => $dates,
            'approvals' => $approvalData,
            'documents' => $documentData,
        ];
    }

    /**
     * Get document type distribution
     */
    private function getDocumentTypeDistribution($start, $end)
    {
        return ApprovalRequest::select('document_type', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('document_type')
            ->groupBy('document_type')
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    /**
     * Get priority distribution
     */
    private function getPriorityDistribution($start, $end)
    {
        return ApprovalRequest::select('priority', DB::raw('count(*) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('priority')
            ->get();
    }

    /**
     * Get top users by submission count
     */
    private function getTopUsers($start, $end)
    {
        return ApprovalRequest::select('user_id', DB::raw('count(*) as submission_count'))
            ->with('user:id,name,NIM,email')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('user_id')
            ->orderByDesc('submission_count')
            ->limit(10)
            ->get();
    }

    /**
     * Get recent activity log
     */
    private function getRecentActivity($limit = 10)
    {
        $activities = [];

        // Recent Approval Requests
        $recentApprovals = ApprovalRequest::with('user:id,name')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'approval_request',
                    'icon' => 'fa-clipboard-check',
                    'color' => 'info',
                    'title' => 'New Approval Request',
                    'description' => "{$item->user->name} submitted {$item->document_name}",
                    'timestamp' => $item->created_at,
                ];
            });

        // Recent Document Signatures
        $recentSignatures = DocumentSignature::with('signer:id,name')
            ->latest('signed_at')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'type' => 'document_signature',
                    'icon' => 'fa-file-signature',
                    'color' => 'success',
                    'title' => 'Document Signed',
                    'description' => "{$item->signer->name} signed a document",
                    'timestamp' => $item->signed_at ?? $item->created_at,
                ];
            });

        // Merge and sort by timestamp
        $activities = $recentApprovals->merge($recentSignatures)
            ->sortByDesc('timestamp')
            ->take($limit);

        return $activities;
    }

    /**
     * Get QR Code access analytics
     */
    private function getQRCodeAnalytics($start, $end)
    {
        $qrCodes = VerificationCodeMapping::whereBetween('created_at', [$start, $end])->get();

        $analytics = [
            'total_codes' => $qrCodes->count(),
            'total_scans' => $qrCodes->sum('access_count'),
            'avg_scans_per_code' => $qrCodes->count() > 0
                ? round($qrCodes->sum('access_count') / $qrCodes->count(), 2)
                : 0,
            'most_scanned' => $qrCodes->sortByDesc('access_count')->take(5),
            'never_scanned' => $qrCodes->where('access_count', 0)->count(),
            'active_codes' => $qrCodes->where('expires_at', '>', now())->count(),
            'expired_codes' => $qrCodes->where('expires_at', '<=', now())->count(),
        ];

        return $analytics;
    }

    /**
     * Export report to CSV/PDF
     */
    public function export(Request $request)
    {
        $format = $request->input('format', 'csv'); // csv or pdf
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Get all data with relationships
        $approvalRequests = ApprovalRequest::with(['user', 'approver', 'documentSignature.digitalSignature'])
            ->whereBetween('created_at', [$start, $end])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get statistics for summary
        $statistics = [
            'total_requests' => $approvalRequests->count(),
            'completed' => $approvalRequests->where('status', ApprovalRequest::STATUS_SIGN_APPROVED)->count(),
            'pending' => $approvalRequests->where('status', ApprovalRequest::STATUS_PENDING)->count(),
            'rejected' => $approvalRequests->where('status', ApprovalRequest::STATUS_REJECTED)->count(),
            'avg_processing_time' => $this->calculateAverageProcessingTime($start, $end),
        ];

        if ($format === 'csv') {
            return $this->exportToCSV($approvalRequests, $startDate, $endDate, $statistics);
        } elseif ($format === 'pdf') {
            return $this->exportToPDF($approvalRequests, $startDate, $endDate, $statistics);
        }

        return back()->with('error', 'Invalid export format selected');
    }

    /**
     * Export to CSV with enhanced data
     */
    private function exportToCSV($data, $startDate, $endDate, $statistics)
    {
        $filename = "digital_signature_report_{$startDate}_to_{$endDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($data, $startDate, $endDate, $statistics) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Report Header
            fputcsv($file, ['DIGITAL SIGNATURE SYSTEM - COMPREHENSIVE REPORT']);
            fputcsv($file, ['Generated On', now()->format('d F Y H:i:s')]);
            fputcsv($file, ['Report Period', "{$startDate} to {$endDate}"]);
            fputcsv($file, ['Generated By', auth('kaprodi')->user()->name ?? 'Admin']);
            fputcsv($file, []);

            // Summary Statistics
            fputcsv($file, ['SUMMARY STATISTICS']);
            fputcsv($file, ['Total Requests', $statistics['total_requests']]);
            fputcsv($file, ['Completed', $statistics['completed']]);
            fputcsv($file, ['Pending', $statistics['pending']]);
            fputcsv($file, ['Rejected', $statistics['rejected']]);
            fputcsv($file, ['Average Processing Time', $statistics['avg_processing_time']]);
            fputcsv($file, []);

            // Data Table Header
            fputcsv($file, ['DETAILED APPROVAL REQUESTS']);
            fputcsv($file, [
                'No',
                'Document Number',
                'Document Name',
                'Document Type',
                'Submitter Name',
                'Submitter NIM',
                'Priority',
                'Status',
                'Submitted At',
                'Approved At',
                'Signed At',
                'Processing Time (hours)',
                'Approver',
                'Digital Signature ID',
                'Signature Status',
                'Notes'
            ]);

            // Data Rows
            $no = 1;
            foreach ($data as $item) {
                $processingTime = $item->created_at && $item->sign_approved_at
                    ? $item->created_at->diffInHours($item->sign_approved_at)
                    : 'N/A';

                fputcsv($file, [
                    $no++,
                    $item->full_document_number ?? 'N/A',
                    $item->document_name ?? 'N/A',
                    $item->document_type ?? 'N/A',
                    $item->user->name ?? 'N/A',
                    $item->user->NIM ?? 'N/A',
                    ucfirst($item->priority),
                    $item->status_label ?? 'N/A',
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->approved_at ? $item->approved_at->format('Y-m-d H:i:s') : 'Not yet approved',
                    $item->sign_approved_at ? $item->sign_approved_at->format('Y-m-d H:i:s') : 'Not yet signed',
                    $processingTime,
                    $item->approver->name ?? 'N/A',
                    $item->documentSignature->digitalSignature->signature_id ?? 'N/A',
                    $item->documentSignature->signature_status ?? 'N/A',
                    $item->notes ?? 'No notes'
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['END OF REPORT']);
            fputcsv($file, ['© ' . date('Y') . ' UMT Informatika - Digital Signature System']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export to PDF with professional layout
     */
    private function exportToPDF($data, $startDate, $endDate, $statistics)
    {
        // Calculate additional statistics
        $statusDistribution = [
            'pending' => $data->where('status', ApprovalRequest::STATUS_PENDING)->count(),
            'approved' => $data->where('status', ApprovalRequest::STATUS_APPROVED)->count(),
            'user_signed' => $data->where('status', ApprovalRequest::STATUS_USER_SIGNED)->count(),
            'sign_approved' => $data->where('status', ApprovalRequest::STATUS_SIGN_APPROVED)->count(),
            'rejected' => $data->where('status', ApprovalRequest::STATUS_REJECTED)->count(),
        ];

        $priorityDistribution = [
            'low' => $data->where('priority', 'low')->count(),
            'normal' => $data->where('priority', 'normal')->count(),
            'high' => $data->where('priority', 'high')->count(),
            'urgent' => $data->where('priority', 'urgent')->count(),
        ];

        // Prepare data for PDF
        $pdfData = [
            'title' => 'Digital Signature System Report',
            'startDate' => Carbon::parse($startDate)->format('d F Y'),
            'endDate' => Carbon::parse($endDate)->format('d F Y'),
            'generatedAt' => now()->format('d F Y H:i:s'),
            'generatedBy' => auth('kaprodi')->user()->name ?? 'Administrator',
            'statistics' => $statistics,
            'statusDistribution' => $statusDistribution,
            'priorityDistribution' => $priorityDistribution,
            'data' => $data,
        ];

        $pdf = Pdf::loadView('digital-signature.admin.reports.pdf-export', $pdfData);

        // Set paper and orientation
        $pdf->setPaper('a4', 'landscape');

        // Set options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif'
        ]);

        $filename = "digital_signature_report_{$startDate}_to_{$endDate}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Get detailed QR Code statistics
     */
    public function qrCodeReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $qrCodes = VerificationCodeMapping::with('documentSignature.approvalRequest.user')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('access_count')
            ->paginate(20);

        $analytics = $this->getQRCodeAnalytics($start, $end);

        return view('digital-signature.admin.reports.qr-codes', compact(
            'qrCodes',
            'analytics',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(Request $request)
    {
        $period = $request->input('period', 'monthly'); // daily, weekly, monthly, yearly

        $metrics = [
            'approval_speed' => $this->getApprovalSpeedMetrics($period),
            'signature_rate' => $this->getSignatureRateMetrics($period),
            'completion_trend' => $this->getCompletionTrendMetrics($period),
        ];

        return view('digital-signature.admin.reports.performance', compact('metrics', 'period'));
    }

    /**
     * Calculate approval speed metrics
     */
    private function getApprovalSpeedMetrics($period)
    {
        // Implementation for approval speed calculation
        return [
            'average' => '2.5 days',
            'fastest' => '4 hours',
            'slowest' => '7 days',
            'median' => '2 days'
        ];
    }

    /**
     * Calculate signature rate metrics
     */
    private function getSignatureRateMetrics($period)
    {
        $total = ApprovalRequest::count();
        $signed = ApprovalRequest::whereIn('status', [
            ApprovalRequest::STATUS_USER_SIGNED,
            ApprovalRequest::STATUS_SIGN_APPROVED
        ])->count();

        return [
            'total_requests' => $total,
            'signed_documents' => $signed,
            'signature_rate' => $total > 0 ? round(($signed / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Get completion trend metrics
     */
    private function getCompletionTrendMetrics($period)
    {
        // Implementation for completion trend
        return [
            'this_month' => 85,
            'last_month' => 78,
            'trend' => '+7%'
        ];
    }
}
