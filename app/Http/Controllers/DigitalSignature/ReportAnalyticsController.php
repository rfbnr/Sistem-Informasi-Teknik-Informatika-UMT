<?php

namespace App\Http\Controllers\DigitalSignature;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ApprovalRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\DigitalSignature;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\DB;
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
            'documents_pending' => DocumentSignature::where('signature_status', 'pending')
                ->whereBetween('created_at', [$start, $end])->count(),
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

        // Approval Rejection Rate
        $statistics['approval_rejection_rate'] = $totalRequests > 0
            ? round(($statistics['approval_rejected'] / $totalRequests) * 100, 2)
            : 0;

        // Document Signature Invalid Rate (replaced rejection which doesn't exist)
        $totalDocSignatures = $statistics['total_document_signatures'];
        $statistics['signature_invalid_rate'] = $totalDocSignatures > 0
            ? round(($statistics['documents_invalid'] / $totalDocSignatures) * 100, 2)
            : 0;

        // Average Processing Time
        $statistics['avg_processing_time'] = $this->calculateAverageProcessingTime($start, $end);

        // Timeline Data (Last 30 days)
        $timelineData = $this->getTimelineData($start, $end);

        // Document Type Distribution
        $documentTypeDistribution = $this->getDocumentTypeDistribution($start, $end);

        // Top Users Statistics
        $topUsers = $this->getTopUsers($start, $end);

        // Top Rejection Reasons
        $topRejectionReasons = $this->getTopRejectionReasons($start, $end);

        // Recent Activity
        $recentActivity = $this->getRecentActivity(5);

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
            'topUsers',
            'topRejectionReasons',
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
     * Get top rejection reasons analytics
     * Only from ApprovalRequest (DocumentSignature doesn't have rejection fields)
     */
    private function getTopRejectionReasons($start, $end)
    {
        // Get rejection reasons from ApprovalRequest only
        $approvalRejections = ApprovalRequest::select('rejection_reason', DB::raw('count(*) as count'))
            ->where('status', ApprovalRequest::STATUS_REJECTED)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('rejection_reason')
            ->where('rejection_reason', '!=', '')
            ->groupBy('rejection_reason')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Add category to each rejection reason
        $result = $approvalRejections->map(function($item) {
            return (object)[
                'rejection_reason' => $item->rejection_reason,
                'count' => $item->count,
                'category' => $this->categorizeRejectionReason($item->rejection_reason)
            ];
        });

        return $result;
    }

    /**
     * Categorize rejection reason for better analytics
     */
    private function categorizeRejectionReason($reason)
    {
        $reason = strtolower($reason);

        if (str_contains($reason, 'placement') || str_contains($reason, 'position')) {
            return 'Signature Placement';
        } elseif (str_contains($reason, 'size') || str_contains($reason, 'large') || str_contains($reason, 'small')) {
            return 'Signature Size';
        } elseif (str_contains($reason, 'quality') || str_contains($reason, 'distorted') || str_contains($reason, 'pixelated')) {
            return 'Signature Quality';
        } elseif (str_contains($reason, 'designated') || str_contains($reason, 'area')) {
            return 'Wrong Area';
        } elseif (str_contains($reason, 'document') || str_contains($reason, 'content')) {
            return 'Document Issue';
        } else {
            return 'Other';
        }
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
            ->whereNotNull('signed_at')
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

        // Recent Rejections
        // $recentRejections = DocumentSignature::with('rejector:id,name', 'approvalRequest:id,document_name')
        //     ->where('signature_status', DocumentSignature::STATUS_REJECTED)
        //     ->whereNotNull('rejected_at')
        //     ->latest('rejected_at')
        //     ->limit($limit)
        //     ->get()
        //     ->map(function ($item) {
        //         $description = $item->rejector
        //             ? "{$item->rejector->name} rejected signature"
        //             : "Signature rejected";

        //         if ($item->approvalRequest) {
        //             $description .= " for {$item->approvalRequest->document_name}";
        //         }

        //         return [
        //             'type' => 'signature_rejection',
        //             'icon' => 'fa-times-circle',
        //             'color' => 'danger',
        //             'title' => 'Signature Rejected',
        //             'description' => $description,
        //             'timestamp' => $item->rejected_at,
        //         ];
        //     });

        // Merge and sort by timestamp
        $activities = $recentApprovals
            ->merge($recentSignatures)
            // ->merge($recentRejections)
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

        // Get all data with relationships (removed non-existent rejector relationship)
        $approvalRequests = ApprovalRequest::with([
                'user',
                'approver',
                'rejector',
                'documentSignature.digitalSignature',
                'documentSignature.signer'
            ])
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
                'Document Name',
                'Document Type',
                'Submitter Name',
                'Submitter NIM',
                'Status',
                'Submitted At',
                'Approved At',
                'Signed At',
                'Final Approval At',
                'Processing Time (hours)',
                'Approver',
                'Digital Signature ID',
                'Signature Status',
                'Rejected At',
                'Rejected By',
                'Rejection Reason',
                'Notes'
            ]);

            // Data Rows
            $no = 1;
            foreach ($data as $item) {
                $processingTime = $item->created_at && $item->sign_approved_at
                    ? $item->created_at->diffInHours($item->sign_approved_at)
                    : 'N/A';

                // Rejection data only from ApprovalRequest (not DocumentSignature)
                $rejectedAt = 'N/A';
                $rejectedBy = 'N/A';
                $rejectionReason = 'N/A';

                if ($item->status === ApprovalRequest::STATUS_REJECTED) {
                    $rejectedAt = $item->rejected_at
                        ? $item->rejected_at->format('Y-m-d H:i:s')
                        : 'N/A';
                    $rejectedBy = $item->rejector->name ?? 'N/A';
                    $rejectionReason = $item->rejection_reason ?? 'N/A';
                }

                fputcsv($file, [
                    $no++,
                    $item->document_name ?? 'N/A',
                    $item->document_type ?? 'General',
                    $item->user->name ?? 'N/A',
                    $item->user->NIM ?? 'N/A',
                    $item->status_label,
                    $item->created_at->format('Y-m-d H:i:s'),
                    $item->approved_at ? $item->approved_at->format('Y-m-d H:i:s') : 'Not yet approved',
                    $item->user_signed_at ? $item->user_signed_at->format('Y-m-d H:i:s') : 'Not yet signed',
                    $item->sign_approved_at ? $item->sign_approved_at->format('Y-m-d H:i:s') : 'Not yet final approved',
                    $processingTime,
                    $item->approver->name ?? 'N/A',
                    $item->documentSignature->digitalSignature->signature_id ?? 'N/A',
                    $item->documentSignature->signature_status ?? 'N/A',
                    $rejectedAt,
                    $rejectedBy,
                    $rejectionReason,
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

        // Prepare data for PDF
        $pdfData = [
            'title' => 'Digital Signature System Report',
            'startDate' => Carbon::parse($startDate)->format('d F Y'),
            'endDate' => Carbon::parse($endDate)->format('d F Y'),
            'generatedAt' => now()->format('d F Y H:i:s'),
            'generatedBy' => auth('kaprodi')->user()->name ?? 'Administrator',
            'statistics' => $statistics,
            'statusDistribution' => $statusDistribution,
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
     * Calculate approval speed metrics (REAL calculation based on actual data)
     */
    private function getApprovalSpeedMetrics($period)
    {
        // Get all approved requests with both created_at and approved_at
        $approvedRequests = ApprovalRequest::whereIn('status', [
                ApprovalRequest::STATUS_APPROVED,
                ApprovalRequest::STATUS_USER_SIGNED,
                ApprovalRequest::STATUS_SIGN_APPROVED
            ])
            ->whereNotNull('approved_at')
            ->get();

        if ($approvedRequests->count() === 0) {
            return [
                'average' => '0 hours',
                'fastest' => 'N/A',
                'slowest' => 'N/A',
                'median' => 'N/A',
                'total_approved' => 0
            ];
        }

        // Calculate time differences in hours
        $timeToApproval = $approvedRequests->map(function($request) {
            return $request->created_at->diffInHours($request->approved_at);
        })->sort()->values();

        $avgHours = $timeToApproval->average();
        $fastestHours = $timeToApproval->min();
        $slowestHours = $timeToApproval->max();
        $medianHours = $timeToApproval->median();

        return [
            'average' => $this->formatHours($avgHours),
            'fastest' => $this->formatHours($fastestHours),
            'slowest' => $this->formatHours($slowestHours),
            'median' => $this->formatHours($medianHours),
            'total_approved' => $approvedRequests->count()
        ];
    }

    /**
     * Format hours into readable string
     */
    private function formatHours($hours)
    {
        if ($hours < 1) {
            return round($hours * 60) . ' minutes';
        }

        $days = floor($hours / 24);
        $remainingHours = round($hours % 24);

        if ($days > 0) {
            return $days . ' days ' . $remainingHours . ' hours';
        }

        return round($hours) . ' hours';
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
     * Get completion trend metrics (REAL calculation)
     */
    private function getCompletionTrendMetrics($period)
    {
        // Calculate completion rate for this month
        $thisMonthStart = now()->startOfMonth();
        $thisMonthEnd = now()->endOfMonth();

        $thisMonthTotal = ApprovalRequest::whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();
        $thisMonthCompleted = ApprovalRequest::where('status', ApprovalRequest::STATUS_SIGN_APPROVED)
            ->whereBetween('created_at', [$thisMonthStart, $thisMonthEnd])->count();

        $thisMonthRate = $thisMonthTotal > 0 ? round(($thisMonthCompleted / $thisMonthTotal) * 100, 1) : 0;

        // Calculate completion rate for last month
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $lastMonthTotal = ApprovalRequest::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        $lastMonthCompleted = ApprovalRequest::where('status', ApprovalRequest::STATUS_SIGN_APPROVED)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $lastMonthRate = $lastMonthTotal > 0 ? round(($lastMonthCompleted / $lastMonthTotal) * 100, 1) : 0;

        // Calculate trend
        $trendDiff = $thisMonthRate - $lastMonthRate;
        $trendFormatted = $trendDiff > 0 ? "+{$trendDiff}%" : "{$trendDiff}%";

        return [
            'this_month' => $thisMonthRate,
            'last_month' => $lastMonthRate,
            'trend' => $trendFormatted,
            'this_month_total' => $thisMonthTotal,
            'this_month_completed' => $thisMonthCompleted,
            'last_month_total' => $lastMonthTotal,
            'last_month_completed' => $lastMonthCompleted
        ];
    }
}
