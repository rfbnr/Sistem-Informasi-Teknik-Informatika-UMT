<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Limited - Digital Signature Verification</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #0056b3 0%, #0056b3 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .rate-limit-container {
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }

        .rate-limit-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .rate-limit-header {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .rate-limit-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: pulse-slow 2s ease-in-out infinite;
        }

        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }

        .rate-limit-body {
            padding: 2rem;
        }

        .countdown-container {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            background: linear-gradient(135deg, #0056b3 0%, #0056b3 100%);
            color: white;
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            margin: 1.5rem 0;
        }

        .countdown-timer {
            font-size: 3rem;
            font-weight: bold;
            font-family: 'Courier New', monospace;
            margin: 1rem 0;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .tips-box {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin: 1.5rem 0;
        }

        .tip-item {
            display: flex;
            align-items: start;
            margin-bottom: 1rem;
        }

        .tip-item:last-child {
            margin-bottom: 0;
        }

        .tip-icon {
            color: #667eea;
            margin-right: 0.75rem;
            margin-top: 0.25rem;
        }

        .progress-bar-animated {
            animation: progress-animation 1s ease-in-out infinite;
        }

        @keyframes progress-animation {
            0% { background-position: 0% 50%; }
            100% { background-position: 100% 50%; }
        }

        .university-logo {
            max-width: 60px;
            height: auto;
            margin-bottom: 0.5rem;
        }

        .btn-retry {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-retry:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
    </style>
</head>
<body>
    <div class="rate-limit-container">
        <div class="rate-limit-card">
            <!-- Rate Limit Header -->
            <div class="rate-limit-header">
                <img src="{{ asset('assets/logo.JPG') }}" alt="Logo UMT" class="university-logo">
                <div class="rate-limit-icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <h2 class="mb-2">Too Many Requests</h2>
                <p class="mb-0">Please wait before trying again</p>
            </div>

            <!-- Rate Limit Body -->
            <div class="rate-limit-body">
                <div class="info-box">
                    <h6><i class="fas fa-shield-alt me-2"></i>Security Notice:</h6>
                    <p class="mb-0">
                        To protect our verification system from abuse, we limit the number of verification
                        attempts from each IP address. You've exceeded the rate limit.
                    </p>
                </div>

                <!-- Countdown Timer -->
                <div class="countdown-container">
                    <div>
                        <i class="fas fa-clock fa-2x mb-2"></i>
                    </div>
                    <div>Please wait</div>
                    <div class="countdown-timer" id="countdown">
                        {{ $seconds ?? 300 }}
                    </div>
                    <div>seconds before retrying</div>

                    <!-- Progress Bar -->
                    <div class="progress mt-3" style="height: 8px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             id="progressBar"
                             style="width: 100%; background: linear-gradient(90deg, #ffffff, rgba(255,255,255,0.5));">
                        </div>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips-box">
                    <h6 class="mb-3">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        Tips to Avoid Rate Limiting:
                    </h6>

                    <div class="tip-item">
                        <i class="fas fa-check-circle tip-icon"></i>
                        <div>
                            <strong>Verify once:</strong> Each document only needs to be verified once to confirm authenticity.
                        </div>
                    </div>

                    <div class="tip-item">
                        <i class="fas fa-check-circle tip-icon"></i>
                        <div>
                            <strong>Use valid tokens:</strong> Ensure your QR code or verification link is correct before submitting.
                        </div>
                    </div>

                    <div class="tip-item">
                        <i class="fas fa-check-circle tip-icon"></i>
                        <div>
                            <strong>Check your connection:</strong> A stable internet connection helps prevent repeated failed attempts.
                        </div>
                    </div>

                    <div class="tip-item">
                        <i class="fas fa-check-circle tip-icon"></i>
                        <div>
                            <strong>Save results:</strong> Take a screenshot or save the verification result for your records.
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div class="text-center">
                    <button class="btn btn-retry btn-lg" id="retryBtn" disabled>
                        <i class="fas fa-redo me-2"></i>
                        <span id="btnText">Please wait...</span>
                    </button>

                    <div class="mt-3">
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-home me-2"></i>
                            Back to Home
                        </a>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="alert alert-info mt-4 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Need immediate assistance?</strong><br>
                    <small>
                        If you believe this is an error or need urgent verification,
                        please contact the Prodi Teknik Informatika administration.
                    </small>
                </div>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center mt-3">
            <small class="text-white">
                <i class="fas fa-shield-alt me-1"></i>
                Digital Signature Verification System - Prodi Teknik Informatika UMT
            </small>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const initialSeconds = {{ $seconds ?? 300 }};
        const totalSeconds = 300; // Always 5 minutes for progress bar calculation

        // Calculate absolute expiry timestamp
        const expiryTimestamp = Date.now() + (initialSeconds * 1000);

        const countdownElement = document.getElementById('countdown');
        const progressBar = document.getElementById('progressBar');
        const retryBtn = document.getElementById('retryBtn');
        const btnText = document.getElementById('btnText');

        // Format time as MM:SS
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }

        function getRemainingSeconds() {
            const now = Date.now();
            const remaining = Math.max(0, Math.ceil((expiryTimestamp - now) / 1000));
            return remaining;
        }

        // Update countdown
        function updateCountdown() {
            const secondsRemaining = getRemainingSeconds();

            if (secondsRemaining > 0) {
                countdownElement.textContent = formatTime(secondsRemaining);

                const elapsedSeconds = totalSeconds - secondsRemaining;
                const progress = Math.min(100, (elapsedSeconds / totalSeconds) * 100);
                progressBar.style.width = progress + '%';

                setTimeout(updateCountdown, 1000);
            } else {
                // Enable retry button
                countdownElement.textContent = '0:00';
                progressBar.style.width = '100%';
                progressBar.classList.remove('progress-bar-animated');
                progressBar.style.background = '#28a745';

                retryBtn.disabled = false;
                retryBtn.classList.add('btn-success');
                btnText.innerHTML = 'Retry Verification Now';

                // Auto-redirect after 2 seconds
                setTimeout(() => {
                    window.location.href = '{{ route("signature.verify.page") }}';
                }, 2000);
            }
        }

        // Start countdown
        updateCountdown();

        // Retry button handler
        retryBtn.addEventListener('click', function() {
            if (!this.disabled) {
                window.location.href = '{{ route("signature.verify.page") }}';
            }
        });

        window.addEventListener('beforeunload', function(e) {
            const remaining = getRemainingSeconds();
            if (remaining > 0) {
                e.preventDefault();
                e.returnValue = 'Your countdown timer will be reset if you leave this page.';
                return e.returnValue;
            }
        });

        // SessionStorage logic (no longer needed with absolute timestamp)
        // The countdown is now based on absolute time, so it's always accurate
        // even if user refreshes the page or hits the endpoint again
    </script>
</body>
</html>
