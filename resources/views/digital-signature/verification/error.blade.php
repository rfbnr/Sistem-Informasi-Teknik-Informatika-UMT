{{-- <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Error - Digital Signature</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-container {
            max-width: 600px;
            width: 100%;
            padding: 2rem;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .error-header {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }

        .error-body {
            padding: 2rem;
        }

        .error-details {
            background: #f8f9fa;
            border-left: 4px solid #dc3545;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .suggestions {
            background: #e7f3ff;
            border-left: 4px solid #0066cc;
            padding: 1rem;
            border-radius: 0.5rem;
            margin: 1.5rem 0;
        }

        .suggestions ul {
            margin-bottom: 0;
            padding-left: 1.5rem;
        }

        .suggestions li {
            margin-bottom: 0.5rem;
        }

        .btn-group-custom {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn-group-custom .btn {
            flex: 1;
            min-width: 150px;
        }

        .university-logo {
            max-width: 60px;
            height: auto;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <!-- Error Header -->
            <div class="error-header">
                <img src="{{ asset('assets/logo.JPG') }}" alt="Logo UMT" class="university-logo">
                <div class="error-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h2 class="mb-2">Verification Error</h2>
                <p class="mb-0">Unable to Verify Digital Signature</p>
            </div>

            <!-- Error Body -->
            <div class="error-body">
                <div class="error-details">
                    <h6><i class="fas fa-info-circle me-2"></i>Error Details:</h6>
                    <p class="mb-0">{{ $message ?? 'Invalid or expired verification link' }}</p>
                </div>

                <div class="suggestions">
                    <h6><i class="fas fa-lightbulb me-2"></i>Possible Causes:</h6>
                    <ul>
                        <li>The verification link has expired</li>
                        <li>The QR code or token is invalid</li>
                        <li>The document has been modified after signing</li>
                        <li>The signature has been revoked</li>
                        <li>Network connection issues</li>
                    </ul>
                </div>

                <h6 class="mt-4 mb-3">What to do next:</h6>

                <div class="btn-group-custom">
                    <a href="{{ route('signature.verify.page') }}" class="btn btn-primary">
                        <i class="fas fa-redo me-2"></i>
                        Try Again --}}
