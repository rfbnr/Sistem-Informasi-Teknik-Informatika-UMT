<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            line-height: 1.4;
            color: #333;
        }

        .page {
            padding: 20px;
        }

        /* Header Section */
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #4e73df;
        }

        .header h1 {
            font-size: 18px;
            color: #4e73df;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .header h2 {
            font-size: 14px;
            color: #5a5c69;
            margin-bottom: 3px;
        }

        .header .subtitle {
            font-size: 10px;
            color: #858796;
        }

        .logo {
            width: 60px;
            height: auto;
            margin-bottom: 10px;
        }

        /* Report Info */
        .report-info {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .report-info table {
            width: 100%;
        }

        .report-info td {
            padding: 3px 8px;
            font-size: 9px;
        }

        .report-info td:first-child {
            font-weight: bold;
            width: 150px;
            color: #5a5c69;
        }

        /* Statistics Grid */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-card {
            display: table-cell;
            width: 20%;
            padding: 8px;
            text-align: center;
            border: 1px solid #e3e6f0;
            background: #fff;
        }

        .stat-card .number {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 3px;
        }

        .stat-card .label {
            font-size: 8px;
            color: #858796;
            text-transform: uppercase;
        }

        .stat-card.primary .number { color: #4e73df; }
        .stat-card.success .number { color: #1cc88a; }
        .stat-card.warning .number { color: #f6c23e; }
        .stat-card.danger .number { color: #e74a3b; }
        .stat-card.info .number { color: #36b9cc; }

        /* Distribution Charts */
        .distribution-section {
            margin-bottom: 15px;
        }

        .distribution-title {
            font-size: 11px;
            font-weight: bold;
            color: #5a5c69;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e3e6f0;
        }

        .distribution-grid {
            display: table;
            width: 100%;
        }

        .distribution-item {
            display: table-cell;
            width: 25%;
            padding: 8px;
            text-align: center;
        }

        .distribution-bar {
            background: #f8f9fc;
            border: 1px solid #e3e6f0;
            border-radius: 3px;
            padding: 8px;
        }

        .bar-container {
            background: #e3e6f0;
            height: 60px;
            border-radius: 3px;
            margin-bottom: 5px;
            position: relative;
        }

        .bar-fill {
            background: #4e73df;
            height: 100%;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }

        .bar-label {
            font-size: 9px;
            font-weight: bold;
            color: #5a5c69;
            margin-bottom: 3px;
        }

        .bar-count {
            font-size: 8px;
            color: #858796;
        }

        /* Data Table */
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #4e73df;
            margin: 15px 0 8px 0;
            padding: 5px 10px;
            background: #f8f9fc;
            border-left: 4px solid #4e73df;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
        }

        table.data-table thead {
            background: #4e73df;
            color: white;
        }

        table.data-table th {
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
        }

        table.data-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e3e6f0;
        }

        table.data-table tbody tr:nth-child(even) {
            background: #f8f9fc;
        }

        table.data-table tbody tr:hover {
            background: #eaecf4;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            color: white;
        }

        .badge-success { background: #1cc88a; }
        .badge-warning { background: #f6c23e; color: #000; }
        .badge-danger { background: #e74a3b; }
        .badge-primary { background: #4e73df; }
        .badge-info { background: #36b9cc; }
        .badge-secondary { background: #858796; }

        /* Priority Colors */
        .priority-low { color: #1cc88a; }
        .priority-normal { color: #36b9cc; }
        .priority-high { color: #f6c23e; }
        .priority-urgent { color: #e74a3b; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 7px;
            color: #858796;
            border-top: 1px solid #e3e6f0;
            padding-top: 8px;
        }

        .page-number:after {
            content: counter(page);
        }

        /* Summary Box */
        .summary-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .summary-box h3 {
            font-size: 11px;
            margin-bottom: 8px;
        }

        .summary-box p {
            font-size: 8px;
            line-height: 1.6;
            margin-bottom: 5px;
        }

        /* Truncate long text */
        .truncate {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header">
            <h1>📊 DIGITAL SIGNATURE SYSTEM</h1>
            <h2>Comprehensive Analytics Report</h2>
            <div class="subtitle">Universitas Muhammadiyah Tangerang - Fakultas Teknik Informatika</div>
        </div>

        <!-- Report Information -->
        <div class="report-info">
            <table>
                <tr>
                    <td>Report Period</td>
                    <td>: <strong>{{ $startDate }} - {{ $endDate }}</strong></td>
                    <td>Generated On</td>
                    <td>: <strong>{{ $generatedAt }}</strong></td>
                </tr>
                <tr>
                    <td>Generated By</td>
                    <td>: <strong>{{ $generatedBy }}</strong></td>
                    <td>Report Type</td>
                    <td>: <strong>Full Analytics Report</strong></td>
                </tr>
            </table>
        </div>

        <!-- Executive Summary -->
        <div class="summary-box">
            <h3>📋 EXECUTIVE SUMMARY</h3>
            <p>
                <strong>Total Requests:</strong> {{ $statistics['total_requests'] }} |
                <strong>Completed:</strong> {{ $statistics['completed'] }} |
                <strong>Pending:</strong> {{ $statistics['pending'] }} |
                <strong>Rejected:</strong> {{ $statistics['rejected'] }}
            </p>
            <p>
                <strong>Average Processing Time:</strong> {{ $statistics['avg_processing_time'] }} |
                <strong>Completion Rate:</strong> {{ $statistics['total_requests'] > 0 ? round(($statistics['completed'] / $statistics['total_requests']) * 100, 2) : 0 }}%
            </p>
        </div>

        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stats-row">
                <div class="stat-card primary">
                    <div class="number">{{ $statistics['total_requests'] }}</div>
                    <div class="label">Total Requests</div>
                </div>
                <div class="stat-card success">
                    <div class="number">{{ $statistics['completed'] }}</div>
                    <div class="label">Completed</div>
                </div>
                <div class="stat-card warning">
                    <div class="number">{{ $statistics['pending'] }}</div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-card danger">
                    <div class="number">{{ $statistics['rejected'] }}</div>
                    <div class="label">Rejected</div>
                </div>
                <div class="stat-card info">
                    <div class="number">{{ $statistics['total_requests'] > 0 ? round(($statistics['completed'] / $statistics['total_requests']) * 100) : 0 }}%</div>
                    <div class="label">Success Rate</div>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="distribution-section">
            <div class="distribution-title">📊 STATUS DISTRIBUTION</div>
            <div class="distribution-grid">
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $maxStatus = max(array_values($statusDistribution));
                            $pendingHeight = $maxStatus > 0 ? ($statusDistribution['pending'] / $maxStatus * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $pendingHeight }}%; background: #f6c23e;">
                                {{ $statusDistribution['pending'] }}
                            </div>
                        </div>
                        <div class="bar-label">Pending</div>
                        <div class="bar-count">{{ $statusDistribution['pending'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $approvedHeight = $maxStatus > 0 ? ($statusDistribution['approved'] / $maxStatus * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $approvedHeight }}%; background: #36b9cc;">
                                {{ $statusDistribution['approved'] }}
                            </div>
                        </div>
                        <div class="bar-label">Approved</div>
                        <div class="bar-count">{{ $statusDistribution['approved'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $signedHeight = $maxStatus > 0 ? ($statusDistribution['user_signed'] / $maxStatus * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $signedHeight }}%; background: #1cc88a;">
                                {{ $statusDistribution['user_signed'] }}
                            </div>
                        </div>
                        <div class="bar-label">User Signed</div>
                        <div class="bar-count">{{ $statusDistribution['user_signed'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $completedHeight = $maxStatus > 0 ? ($statusDistribution['sign_approved'] / $maxStatus * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $completedHeight }}%; background: #4e73df;">
                                {{ $statusDistribution['sign_approved'] }}
                            </div>
                        </div>
                        <div class="bar-label">Completed</div>
                        <div class="bar-count">{{ $statusDistribution['sign_approved'] }} requests</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Priority Distribution -->
        <div class="distribution-section">
            <div class="distribution-title">⚡ PRIORITY DISTRIBUTION</div>
            <div class="distribution-grid">
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $maxPriority = max(array_values($priorityDistribution));
                            $lowHeight = $maxPriority > 0 ? ($priorityDistribution['low'] / $maxPriority * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $lowHeight }}%; background: #1cc88a;">
                                {{ $priorityDistribution['low'] }}
                            </div>
                        </div>
                        <div class="bar-label">Low Priority</div>
                        <div class="bar-count">{{ $priorityDistribution['low'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $normalHeight = $maxPriority > 0 ? ($priorityDistribution['normal'] / $maxPriority * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $normalHeight }}%; background: #36b9cc;">
                                {{ $priorityDistribution['normal'] }}
                            </div>
                        </div>
                        <div class="bar-label">Normal</div>
                        <div class="bar-count">{{ $priorityDistribution['normal'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $highHeight = $maxPriority > 0 ? ($priorityDistribution['high'] / $maxPriority * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $highHeight }}%; background: #f6c23e;">
                                {{ $priorityDistribution['high'] }}
                            </div>
                        </div>
                        <div class="bar-label">High Priority</div>
                        <div class="bar-count">{{ $priorityDistribution['high'] }} requests</div>
                    </div>
                </div>
                <div class="distribution-item">
                    <div class="distribution-bar">
                        @php
                            $urgentHeight = $maxPriority > 0 ? ($priorityDistribution['urgent'] / $maxPriority * 100) : 0;
                        @endphp
                        <div class="bar-container">
                            <div class="bar-fill" style="height: {{ $urgentHeight }}%; background: #e74a3b;">
                                {{ $priorityDistribution['urgent'] }}
                            </div>
                        </div>
                        <div class="bar-label">Urgent</div>
                        <div class="bar-count">{{ $priorityDistribution['urgent'] }} requests</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Data Table -->
        <div class="section-title">📑 DETAILED APPROVAL REQUESTS</div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 3%;">No</th>
                    <th style="width: 10%;">Document No</th>
                    <th style="width: 15%;">Document Name</th>
                    <th style="width: 10%;">Submitter</th>
                    <th style="width: 7%;">NIM</th>
                    <th style="width: 7%;">Priority</th>
                    <th style="width: 9%;">Status</th>
                    <th style="width: 10%;">Submitted</th>
                    <th style="width: 10%;">Signed</th>
                    <th style="width: 8%;">Processing</th>
                    <th style="width: 11%;">Approver</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><small>{{ $item->full_document_number ?? 'N/A' }}</small></td>
                    <td class="truncate" title="{{ $item->document_name }}">{{ \Str::limit($item->document_name, 30) }}</td>
                    <td>{{ $item->user->name ?? 'N/A' }}</td>
                    <td><small>{{ $item->user->NIM ?? 'N/A' }}</small></td>
                    <td class="text-center">
                        <span class="priority-{{ $item->priority }}">
                            <strong>{{ strtoupper($item->priority) }}</strong>
                        </span>
                    </td>
                    <td>
                        @if($item->status == 'sign_approved')
                            <span class="badge badge-success">Completed</span>
                        @elseif($item->status == 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($item->status == 'rejected')
                            <span class="badge badge-danger">Rejected</span>
                        @elseif($item->status == 'user_signed')
                            <span class="badge badge-info">Signed</span>
                        @else
                            <span class="badge badge-secondary">{{ $item->status_label }}</span>
                        @endif
                    </td>
                    <td><small>{{ $item->created_at->format('d/m/Y H:i') }}</small></td>
                    <td><small>{{ $item->sign_approved_at ? $item->sign_approved_at->format('d/m/Y H:i') : '-' }}</small></td>
                    <td class="text-center">
                        @if($item->created_at && $item->sign_approved_at)
                            <strong>{{ $item->created_at->diffInHours($item->sign_approved_at) }}h</strong>
                        @else
                            <span class="badge badge-secondary">Pending</span>
                        @endif
                    </td>
                    <td><small>{{ $item->approver->name ?? '-' }}</small></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>
                <strong>Digital Signature System - UMT Informatika</strong> |
                Generated: {{ $generatedAt }} |
                Page <span class="page-number"></span> |
                © {{ date('Y') }} Universitas Muhammadiyah Tangerang
            </p>
            <p style="margin-top: 3px;">
                This is a computer-generated report. No signature required. |
                <strong>CONFIDENTIAL</strong> - For internal use only
            </p>
        </div>
    </div>
</body>
</html>
