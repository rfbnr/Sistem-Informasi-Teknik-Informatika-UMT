<?php

namespace App\Http\Controllers\DigitalSignature;

use App\Http\Controllers\Controller;
use App\Models\SignatureAuditLog;
use App\Models\SignatureVerificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class LogsController extends Controller
{
    /**
     * Redirect to audit logs (default view)
     */
    public function index()
    {
        return redirect()->route('admin.signature.logs.audit');
    }

    /**
     * Display audit logs
     */
    public function auditLogs(Request $request)
    {
        // Get filters from request
        $range = $request->get('range', '7days');
        $action = $request->get('action');
        $device = $request->get('device');
        $status = $request->get('status');

        // Build query
        $query = SignatureAuditLog::with(['user', 'documentSignature', 'approvalRequest'])
            ->latest('performed_at');

        // Apply date range filter
        switch ($range) {
            case 'today':
                $query->today();
                break;
            case '7days':
                $query->lastNDays(7);
                break;
            case '30days':
                $query->lastNDays(30);
                break;
            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    $query->inPeriod($request->start_date, $request->end_date);
                }
                break;
        }

        // Apply action filter
        if ($action) {
            $query->byAction($action);
        }

        // Apply device filter
        if ($device) {
            $query->byDeviceType($device);
        }

        // Apply status filter
        if ($status == 'success') {
            $query->successfulActions();
        } elseif ($status == 'failed') {
            $query->failedActions();
        }

        // Filter by kaprodi (only show their logs)
        if (Auth::guard('kaprodi')->check()) {
            $query->byKaprodi(Auth::id());
        }

        // Paginate results
        $logs = $query->paginate(20)->appends($request->all());

        // Calculate statistics
        $statsQuery = SignatureAuditLog::query();
        if (Auth::guard('kaprodi')->check()) {
            $statsQuery->byKaprodi(Auth::id());
        }

        $stats = [
            'total_today' => (clone $statsQuery)->today()->count(),
            'successful_today' => (clone $statsQuery)->today()->successfulActions()->count(),
            'failed_today' => (clone $statsQuery)->today()->failedActions()->count(),
            'unique_users' => (clone $statsQuery)->today()->distinct('user_id')->count('user_id'),
        ];

        // Calculate success rate
        $total = $stats['total_today'];
        $stats['success_rate'] = $total > 0 ? round(($stats['successful_today'] / $total) * 100, 1) : 100;

        // Get counts for tabs
        $auditCount = SignatureAuditLog::when(Auth::guard('kaprodi')->check(), function($q) {
            return $q->byKaprodi(Auth::id());
        })->lastNDays(7)->count();

        $verificationCount = SignatureVerificationLog::lastNDays(7)->count();

        // Get action types for filter dropdown
        $actionTypes = [
            SignatureAuditLog::ACTION_SIGNATURE_INITIATED => 'Signature Initiated',
            SignatureAuditLog::ACTION_DOCUMENT_SIGNED => 'Document Signed',
            SignatureAuditLog::ACTION_SIGNATURE_VERIFIED => 'Signature Verified',
            SignatureAuditLog::ACTION_TEMPLATE_CREATED => 'Template Created',
            SignatureAuditLog::ACTION_TEMPLATE_UPDATED => 'Template Updated',
            SignatureAuditLog::ACTION_SIGNING_FAILED => 'Signing Failed',
        ];

        return view('digital-signature.admin.logs.audit', compact(
            'logs',
            'stats',
            'auditCount',
            'verificationCount',
            'actionTypes',
            'range',
            'action',
            'device',
            'status'
        ));
    }

    /**
     * Display verification logs
     */
    public function verificationLogs(Request $request)
    {
        // Get filters from request
        $range = $request->get('range', '7days');
        $method = $request->get('method');
        $status = $request->get('status');
        $userType = $request->get('user_type');

        // Build query
        $query = SignatureVerificationLog::with(['user', 'documentSignature', 'approvalRequest'])
            ->latest('verified_at');

        // Apply date range filter
        switch ($range) {
            case 'today':
                $query->today();
                break;
            case '7days':
                $query->lastNDays(7);
                break;
            case '30days':
                $query->lastNDays(30);
                break;
            case 'custom':
                if ($request->has('start_date') && $request->has('end_date')) {
                    $query->inPeriod($request->start_date, $request->end_date);
                }
                break;
        }

        // Apply method filter
        if ($method) {
            $query->byMethod($method);
        }

        // Apply status filter
        if ($status == 'valid') {
            $query->successful();
        } elseif ($status == 'invalid') {
            $query->failed();
        }

        // Apply user type filter
        if ($userType == 'anonymous') {
            $query->anonymous();
        } elseif ($userType == 'authenticated') {
            $query->authenticated();
        }

        // Paginate results
        $logs = $query->paginate(20)->appends($request->all());

        // Calculate statistics
        $stats = [
            'total_today' => SignatureVerificationLog::today()->count(),
            'successful_today' => SignatureVerificationLog::today()->successful()->count(),
            'failed_today' => SignatureVerificationLog::today()->failed()->count(),
            'anonymous_today' => SignatureVerificationLog::today()->anonymous()->count(),
        ];

        // Calculate success rate
        $total = $stats['total_today'];
        $stats['success_rate'] = $total > 0 ? round(($stats['successful_today'] / $total) * 100, 1) : 100;

        // Get counts for tabs
        $auditCount = SignatureAuditLog::when(Auth::guard('kaprodi')->check(), function($q) {
            return $q->byKaprodi(Auth::id());
        })->lastNDays(7)->count();

        $verificationCount = SignatureVerificationLog::lastNDays(7)->count();

        // Method types for filter
        $methodTypes = [
            SignatureVerificationLog::METHOD_TOKEN => 'Token',
            SignatureVerificationLog::METHOD_URL => 'URL',
            SignatureVerificationLog::METHOD_QR => 'QR Code',
            SignatureVerificationLog::METHOD_ID => 'ID',
        ];

        return view('digital-signature.admin.logs.verification', compact(
            'logs',
            'stats',
            'auditCount',
            'verificationCount',
            'methodTypes',
            'range',
            'method',
            'status',
            'userType'
        ));
    }

    /**
     * Show log details (AJAX)
     */
    public function logDetails($id, Request $request)
    {
        $type = $request->get('type', 'audit');

        if ($type === 'audit') {
            $log = SignatureAuditLog::with(['user', 'documentSignature', 'approvalRequest'])
                ->findOrFail($id);

            // Check authorization for kaprodi
            if (Auth::guard('kaprodi')->check() && $log->kaprodi_id != Auth::id()) {
                abort(403, 'Unauthorized access');
            }
        } else {
            $log = SignatureVerificationLog::with(['user', 'documentSignature', 'approvalRequest'])
                ->findOrFail($id);
        }

        return response()->json([
            'success' => true,
            'log' => $log,
            'metadata' => $log->metadata,
        ]);
    }

    /**
     * Export logs to CSV or PDF
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'audit'); // audit or verification
        $format = $request->get('format', 'csv'); // csv or pdf
        $range = $request->get('range', '7days');

        // Build query based on type
        if ($type === 'audit') {
            $query = SignatureAuditLog::with(['user', 'documentSignature'])
                ->when(Auth::guard('kaprodi')->check(), function($q) {
                    return $q->byKaprodi(Auth::id());
                });
        } else {
            $query = SignatureVerificationLog::with(['user', 'documentSignature']);
        }

        // Apply date range
        switch ($range) {
            case 'today':
                $query->whereDate($type === 'audit' ? 'performed_at' : 'verified_at', today());
                break;
            case '7days':
                $query->lastNDays(7);
                break;
            case '30days':
                $query->lastNDays(30);
                break;
        }

        $logs = $query->latest($type === 'audit' ? 'performed_at' : 'verified_at')->get();

        if ($format === 'csv') {
            return $this->exportToCsv($logs, $type);
        } else {
            return $this->exportToPdf($logs, $type);
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCsv($logs, $type)
    {
        $filename = $type . '_logs_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs, $type) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            if ($type === 'audit') {
                fputcsv($file, ['Date', 'Time', 'Action', 'User', 'Status', 'Device', 'Browser', 'Duration', 'Description']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->performed_at->format('Y-m-d'),
                        $log->performed_at->format('H:i:s'),
                        $log->action_label,
                        $log->user->name ?? 'System',
                        $log->is_success ? 'Success' : 'Failed',
                        ucfirst($log->device_type),
                        $log->browser_name,
                        $log->duration_human ?? '-',
                        $log->description,
                    ]);
                }
            } else {
                fputcsv($file, ['Date', 'Time', 'Method', 'User', 'Status', 'Result', 'Device', 'Browser', 'Duration', 'Anonymous']);

                foreach ($logs as $log) {
                    fputcsv($file, [
                        $log->verified_at->format('Y-m-d'),
                        $log->verified_at->format('H:i:s'),
                        $log->method_label,
                        $log->user->name ?? 'Anonymous',
                        $log->is_valid ? 'Valid' : 'Invalid',
                        $log->result_label,
                        ucfirst($log->device_type),
                        $log->browser_name,
                        $log->verification_duration_human ?? '-',
                        $log->is_anonymous ? 'Yes' : 'No',
                    ]);
                }
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Export to PDF (simple version)
     */
    private function exportToPdf($logs, $type)
    {
        // For now, return CSV (PDF export can be added later with a library like dompdf)
        return $this->exportToCsv($logs, $type);
    }

    /**
     * Get recent failures for badge counter
     */
    public static function getRecentFailuresCount()
    {
        return SignatureAuditLog::failedActions()->today()->count();
    }
}
