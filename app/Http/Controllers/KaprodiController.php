<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Signature;
use Illuminate\Http\Request;
use App\Models\SignatureRequest;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Auth;
use App\Models\BlockchainTransaction;

class KaprodiController extends Controller
{
    // protected $blockchainService;

    // public function __construct(BlockchainService $blockchainService)
    // {
    //     $this->blockchainService = $blockchainService;
    // }

    public function dashboard()
    {
        $pendingCount = 5; // Mock data
        $completedCount = 12;
        $blockchainCount = 8;
        $thisMonthCount = 15;
        $growthPercentage = 25;
        $urgentCount = 2;
        $recentRequests = collect([]);
        $recentActivities = collect([]);
        $chartData = ['labels' => [], 'signed' => [], 'pending' => []];
        $blockchainStats = ['pending_tx' => 2, 'confirmed_tx' => 8, 'gas_price' => '25'];
        // dd('Kaprodi Dashboard Data Prepared');
        // dd($recentRequests);
        // dd($recentActivities);
        // dd($chartData);
        // dd($blockchainStats);

        return view('kaprodi.dashboard');

        // return view('kaprodi.dashboard', compact(
        //     'pendingCount', 'completedCount', 'blockchainCount', 'thisMonthCount',
        //     'growthPercentage', 'urgentCount', 'recentRequests', 'recentActivities',
        //     'chartData', 'blockchainStats'
        // ));
    }

    public function signatureIndex()
    {
        $signatureRequests = collect(); // Mock empty collection for now
        $stats = [
            'pending' => 5,
            'in_progress' => 3,
            'completed' => 12,
            'urgent' => 2
        ];

        return view('kaprodi.signatures.index', compact('signatureRequests', 'stats'));
    }

    public function pendingSignatures()
    {
        $pendingRequests = collect(); // Mock empty collection
        $urgentCount = 2;
        $expiringTodayCount = 1;

        return view('kaprodi.signatures.pending', compact(
            'pendingRequests', 'urgentCount', 'expiringTodayCount'
        ));
    }

    public function completedSignatures()
    {
        $completedRequests = collect(); // Mock empty collection
        return view('kaprodi.signatures.completed', compact('completedRequests'));
    }

    public function urgentSignatures()
    {
        $urgentRequests = collect(); // Mock empty collection
        $overdueCount = 1;
        $expiringTodayCount = 1;

        return view('kaprodi.signatures.urgent', compact(
            'urgentRequests', 'overdueCount', 'expiringTodayCount'
        ));
    }

    public function signatureShow(SignatureRequest $signatureRequest)
    {
        // For now, create a mock signature request if none provided
        if (!$signatureRequest->exists) {
            // This would normally throw a 404, but for testing we'll create mock data
            $signatureRequest = new SignatureRequest([
                'id' => 1,
                'title' => 'Mock Document',
                'status' => 'pending',
                'created_at' => now(),
                'is_urgent' => false
            ]);
        }

        return view('kaprodi.signatures.show', compact('signatureRequest'));
    }

    public function blockchainTransactions()
    {
        $transactions = collect(); // Mock empty collection
        $stats = [
            'confirmed' => 8,
            'pending' => 2,
            'total_blocks' => 1500000,
            'avg_gas_price' => 25
        ];

        return view('kaprodi.blockchain.transactions', compact('transactions', 'stats'));
    }

    public function blockchainVerify()
    {
        return view('kaprodi.blockchain.verify');
    }

    public function blockchainStatus()
    {
        return view('kaprodi.blockchain.status');
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $summary = [
            'total_signatures' => 45,
            'completed_documents' => 38,
            'completion_rate' => 84.4,
            'avg_processing_days' => 2.5,
            'fastest_processing' => 1,
            'blockchain_transactions' => 52,
            'blockchain_success_rate' => 96.2,
            'signature_growth' => 15.3
        ];

        $documentTypes = [
            ['name' => 'Surat Keterangan', 'count' => 18, 'percentage' => 40],
            ['name' => 'Surat Rekomendasi', 'count' => 15, 'percentage' => 33.3],
            ['name' => 'Surat Tugas', 'count' => 8, 'percentage' => 17.8],
            ['name' => 'Lainnya', 'count' => 4, 'percentage' => 8.9]
        ];

        $topRequesters = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@example.com', 'count' => 8],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@example.com', 'count' => 6],
            ['name' => 'Budi Santoso', 'email' => 'budi@example.com', 'count' => 5]
        ];

        $statusDistribution = [
            ['status' => 'completed', 'count' => 38, 'percentage' => 84.4],
            ['status' => 'pending', 'count' => 5, 'percentage' => 11.1],
            ['status' => 'in_progress', 'count' => 2, 'percentage' => 4.5]
        ];

        $chartData = [
            'daily_labels' => collect(range(1, 30))->map(fn($day) => "Day $day")->toArray(),
            'daily_signatures' => collect(range(1, 30))->map(fn() => rand(0, 5))->toArray(),
            'time_labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'processing_times' => [2.1, 2.8, 2.3, 2.9]
        ];

        return view('kaprodi.reports.monthly', compact(
            'summary', 'documentTypes', 'topRequesters', 'statusDistribution', 'chartData', 'month'
        ));
    }

    public function quarterlyReport()
    {
        return view('kaprodi.reports.quarterly');
    }

    public function annualReport()
    {
        return view('kaprodi.reports.annual');
    }

    public function getStats()
    {
        return response()->json([
            'pending' => 5,
            'completed' => 12,
            'blockchain' => 8,
            'this_month' => 15
        ]);
    }

    public function getChartData($period)
    {
        return response()->json([
            'labels' => ['Day 1', 'Day 2', 'Day 3'],
            'signed' => [5, 8, 12],
            'pending' => [3, 2, 1]
        ]);
    }

    public function getBlockchainStatus()
    {
        return response()->json(['status' => 'online']);
    }
}
