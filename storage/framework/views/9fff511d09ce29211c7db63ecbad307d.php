<?php $__env->startSection('title', 'Performance Metrics'); ?>

<?php $__env->startSection('sidebar'); ?>
    <?php echo $__env->make('digital-signature.admin.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="mb-2">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    Performance Metrics
                </h1>
                <p class="mb-0 opacity-75">System performance analysis and optimization insights</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="<?php echo e(route('admin.signature.reports.index')); ?>" class="btn btn-outline-light">
                    <i class="fas fa-arrow-left me-1"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>

    <!-- Period Filter -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('admin.signature.reports.performance')); ?>" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Analysis Period</label>
                    <select class="form-select" name="period" onchange="this.form.submit()">
                        <option value="daily" <?php echo e($period == 'daily' ? 'selected' : ''); ?>>Daily</option>
                        <option value="weekly" <?php echo e($period == 'weekly' ? 'selected' : ''); ?>>Weekly</option>
                        <option value="monthly" <?php echo e($period == 'monthly' ? 'selected' : ''); ?>>Monthly</option>
                        <option value="yearly" <?php echo e($period == 'yearly' ? 'selected' : ''); ?>>Yearly</option>
                    </select>
                </div>
                <div class="col-md-9">
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Currently showing <strong><?php echo e(ucfirst($period)); ?></strong> performance metrics
                        </small>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Approval Speed Metrics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Approval Speed Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-lg-2 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <div class="h2 text-primary"><?php echo e($metrics['approval_speed']['average']); ?></div>
                                <div class="text-muted">Average Time</div>
                                <small class="text-muted">From submission to approval</small>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <div class="h2 text-success"><?php echo e($metrics['approval_speed']['fastest']); ?></div>
                                <div class="text-muted">Fastest Approval</div>
                                <small class="text-success">
                                    <i class="fas fa-bolt"></i> Best performance
                                </small>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <div class="h2 text-danger"><?php echo e($metrics['approval_speed']['slowest']); ?></div>
                                <div class="text-muted">Slowest Approval</div>
                                <small class="text-danger">
                                    <i class="fas fa-exclamation-triangle"></i> Needs attention
                                </small>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 mb-3">
                            <div class="p-3 border rounded">
                                <div class="h2 text-info"><?php echo e($metrics['approval_speed']['median']); ?></div>
                                <div class="text-muted">Median Time</div>
                                <small class="text-muted">Middle value</small>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-8 mb-3">
                            <div class="p-3 bg-light rounded">
                                <div class="h4 text-secondary"><?php echo e($metrics['approval_speed']['total_approved']); ?></div>
                                <div class="text-muted">Total Approved Requests</div>
                                <small class="text-muted">Based on actual data</small>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lightbulb fa-2x me-3"></i>
                            <div>
                                <strong>Performance Insight:</strong>
                                <?php if($metrics['approval_speed']['average'] == '2.5 days'): ?>
                                    The average approval time is within acceptable range. Consider optimizing processes
                                    that take longer than 3 days for better efficiency.
                                <?php else: ?>
                                    Monitor approval processes to maintain optimal performance.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Signature Rate Metrics -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-signature me-2"></i>
                        Signature Rate Metrics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="display-3 text-success mb-2">
                            <?php echo e($metrics['signature_rate']['signature_rate']); ?>%
                        </div>
                        <div class="h5 text-muted">Signature Completion Rate</div>
                    </div>

                    <div class="progress mb-4" style="height: 30px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                             style="width: <?php echo e($metrics['signature_rate']['signature_rate']); ?>%">
                            <strong><?php echo e($metrics['signature_rate']['signature_rate']); ?>%</strong>
                        </div>
                    </div>

                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <div class="h3 text-primary"><?php echo e(number_format($metrics['signature_rate']['total_requests'])); ?></div>
                            <small class="text-muted">Total Requests</small>
                        </div>
                        <div class="col-6">
                            <div class="h3 text-success"><?php echo e(number_format($metrics['signature_rate']['signed_documents'])); ?></div>
                            <small class="text-muted">Signed Documents</small>
                        </div>
                    </div>

                    <hr>

                    <div class="alert alert-<?php echo e($metrics['signature_rate']['signature_rate'] >= 80 ? 'success' : ($metrics['signature_rate']['signature_rate'] >= 60 ? 'warning' : 'danger')); ?>">
                        <i class="fas fa-<?php echo e($metrics['signature_rate']['signature_rate'] >= 80 ? 'check-circle' : 'exclamation-triangle'); ?> me-2"></i>
                        <?php if($metrics['signature_rate']['signature_rate'] >= 80): ?>
                            <strong>Excellent!</strong> Your signature completion rate is above 80%.
                        <?php elseif($metrics['signature_rate']['signature_rate'] >= 60): ?>
                            <strong>Good!</strong> Consider improving processes to reach 80%+ completion rate.
                        <?php else: ?>
                            <strong>Attention Needed!</strong> Low signature rate. Review approval workflow.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Trend -->
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Completion Trend
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="h2 text-info"><?php echo e($metrics['completion_trend']['this_month']); ?>%</div>
                                <div class="text-muted">This Month</div>
                                <small class="text-muted">
                                    <?php echo e($metrics['completion_trend']['this_month_completed']); ?>/<?php echo e($metrics['completion_trend']['this_month_total']); ?>

                                </small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded">
                                <div class="h2 text-secondary"><?php echo e($metrics['completion_trend']['last_month']); ?>%</div>
                                <div class="text-muted">Last Month</div>
                                <small class="text-muted">
                                    <?php echo e($metrics['completion_trend']['last_month_completed']); ?>/<?php echo e($metrics['completion_trend']['last_month_total']); ?>

                                </small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-<?php echo e(strpos($metrics['completion_trend']['trend'], '+') !== false ? 'success' : 'warning'); ?>-subtle rounded">
                                <div class="display-4 text-<?php echo e(strpos($metrics['completion_trend']['trend'], '+') !== false ? 'success' : 'warning'); ?>">
                                    <?php echo e($metrics['completion_trend']['trend']); ?>

                                </div>
                                <div class="text-muted">
                                    <?php if(strpos($metrics['completion_trend']['trend'], '+') !== false): ?>
                                        <i class="fas fa-arrow-up text-success"></i> Trend Improvement
                                    <?php else: ?>
                                        <i class="fas fa-arrow-down text-warning"></i> Trend Change
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <canvas id="trendChart" height="150"></canvas>

                    <hr>

                    <div class="alert alert-<?php echo e(strpos($metrics['completion_trend']['trend'], '+') !== false ? 'success' : 'warning'); ?>">
                        <i class="fas fa-<?php echo e(strpos($metrics['completion_trend']['trend'], '+') !== false ? 'thumbs-up' : 'info-circle'); ?> me-2"></i>
                        <?php if(strpos($metrics['completion_trend']['trend'], '+') !== false): ?>
                            <strong>Positive Trend!</strong> Performance is improving month-over-month.
                        <?php else: ?>
                            <strong>Monitor Closely!</strong> Performance trend needs attention.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Benchmarks -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-medal me-2"></i>
                        Performance Benchmarks
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Metric</th>
                                    <th>Current</th>
                                    <th>Target</th>
                                    <th>Benchmark</th>
                                    <th>Status</th>
                                    <th width="200">Progress</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <strong>Approval Speed</strong>
                                        <br><small class="text-muted">Average processing time</small>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo e($metrics['approval_speed']['average']); ?></span></td>
                                    <td><span class="badge bg-success">≤ 2 days</span></td>
                                    <td><span class="badge bg-info">2-3 days</span></td>
                                    <td>
                                        <span class="badge bg-warning">
                                            <i class="fas fa-clock"></i> Good
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning" style="width: 80%">80%</div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Signature Rate</strong>
                                        <br><small class="text-muted">Completion percentage</small>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo e($metrics['signature_rate']['signature_rate']); ?>%</span></td>
                                    <td><span class="badge bg-success">≥ 85%</span></td>
                                    <td><span class="badge bg-info">80-90%</span></td>
                                    <td>
                                        <?php if($metrics['signature_rate']['signature_rate'] >= 85): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Excellent
                                            </span>
                                        <?php elseif($metrics['signature_rate']['signature_rate'] >= 70): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-circle"></i> Good
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle"></i> Needs Improvement
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-<?php echo e($metrics['signature_rate']['signature_rate'] >= 85 ? 'success' : 'warning'); ?>"
                                                 style="width: <?php echo e($metrics['signature_rate']['signature_rate']); ?>%">
                                                <?php echo e($metrics['signature_rate']['signature_rate']); ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <strong>Monthly Growth</strong>
                                        <br><small class="text-muted">Trend comparison</small>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo e($metrics['completion_trend']['trend']); ?></span></td>
                                    <td><span class="badge bg-success">≥ +5%</span></td>
                                    <td><span class="badge bg-info">+3% to +10%</span></td>
                                    <td>
                                        <?php if(str_replace(['%', '+'], '', $metrics['completion_trend']['trend']) >= 5): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Excellent
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-info-circle"></i> Moderate
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: 90%">90%</div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recommendations -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>
                        Performance Recommendations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h6 class="text-primary">
                                        <i class="fas fa-bolt me-2"></i>
                                        Speed Optimization
                                    </h6>
                                    <ul class="mb-0">
                                        <li>Set SLA for approval process (target: ≤ 2 days)</li>
                                        <li>Implement automated notifications for pending approvals</li>
                                        <li>Review and streamline approval workflow</li>
                                        <li>Consider delegating authority for routine approvals</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card border-success">
                                <div class="card-body">
                                    <h6 class="text-success">
                                        <i class="fas fa-chart-line me-2"></i>
                                        Completion Rate Improvement
                                    </h6>
                                    <ul class="mb-0">
                                        <li>Follow up on pending signature requests</li>
                                        <li>Send reminder notifications to users</li>
                                        <li>Provide clear instructions for document signing</li>
                                        <li>Identify and resolve common bottlenecks</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-body">
                                    <h6 class="text-info">
                                        <i class="fas fa-users me-2"></i>
                                        User Experience
                                    </h6>
                                    <ul class="mb-0">
                                        <li>Simplify the submission process</li>
                                        <li>Provide templates for common document types</li>
                                        <li>Offer training sessions for new users</li>
                                        <li>Implement mobile-friendly signing</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="card border-warning">
                                <div class="card-body">
                                    <h6 class="text-warning">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        Quality Assurance
                                    </h6>
                                    <ul class="mb-0">
                                        <li>Regular audit of signed documents</li>
                                        <li>Monitor rejection reasons and patterns</li>
                                        <li>Implement quality checks before submission</li>
                                        <li>Track and address common errors</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Trend Chart
const trendCtx = document.getElementById('trendChart').getContext('2d');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Last Month', 'This Month'],
        datasets: [{
            label: 'Completion Rate',
            data: [<?php echo e($metrics['completion_trend']['last_month']); ?>, <?php echo e($metrics['completion_trend']['this_month']); ?>],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('digital-signature.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/porto-mac/Documents/GitHub/web-umt/resources/views/digital-signature/admin/reports/performance.blade.php ENDPATH**/ ?>