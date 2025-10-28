<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'UMT Informatika - Digital Signature System' }}</title>
    <style>
        /* Reset Styles */
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f7fa;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        img {
            border: 0;
            outline: none;
            text-decoration: none;
            display: block;
        }

        a {
            text-decoration: none;
        }

        /* Container */
        .email-wrapper {
            width: 100%;
            background-color: #f4f7fa;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 40px;
            text-align: center;
        }

        .email-header h1 {
            margin: 0;
            padding: 0;
            color: #ffffff;
            font-size: 24px;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .email-header .logo {
            margin-bottom: 15px;
        }

        .email-header .subtitle {
            margin: 10px 0 0 0;
            padding: 0;
            color: rgba(255, 255, 255, 0.9);
            font-size: 14px;
            font-weight: 400;
        }

        /* Content */
        .email-content {
            padding: 40px;
            color: #2c3e50;
            line-height: 1.6;
        }

        .email-content h2 {
            margin: 0 0 20px 0;
            padding: 0;
            color: #2c3e50;
            font-size: 20px;
            font-weight: 600;
        }

        .email-content p {
            margin: 0 0 16px 0;
            padding: 0;
            color: #555555;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Greeting */
        .greeting {
            font-size: 16px;
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 20px;
        }

        /* Card Component */
        .card {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .card-title {
            margin: 0 0 12px 0;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        .card-content {
            margin: 0;
            color: #555555;
            font-size: 14px;
        }

        /* Info Table */
        .info-table {
            width: 100%;
            margin: 20px 0;
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            overflow: hidden;
        }

        .info-table tr {
            border-bottom: 1px solid #e9ecef;
        }

        .info-table tr:last-child {
            border-bottom: none;
        }

        .info-table td {
            padding: 12px 16px;
            font-size: 14px;
        }

        .info-table td:first-child {
            font-weight: 600;
            color: #2c3e50;
            width: 40%;
            background-color: #f8f9fa;
        }

        .info-table td:last-child {
            color: #555555;
            width: 60%;
        }

        /* Button Styles */
        .button {
            display: inline-block;
            padding: 14px 32px;
            margin: 20px 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .button-secondary {
            background: #ffffff;
            color: #667eea !important;
            border: 2px solid #667eea;
        }

        .button-block {
            display: block;
            width: 100%;
            box-sizing: border-box;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .badge-primary {
            background-color: #e3e8ff;
            color: #667eea;
        }

        /* Divider */
        .divider {
            height: 1px;
            background-color: #e9ecef;
            margin: 30px 0;
        }

        /* Footer */
        .email-footer {
            background-color: #2c3e50;
            padding: 30px 40px;
            text-align: center;
            color: #ffffff;
        }

        .email-footer p {
            margin: 0 0 10px 0;
            padding: 0;
            color: rgba(255, 255, 255, 0.8);
            font-size: 13px;
            line-height: 1.5;
        }

        .email-footer a {
            color: #667eea;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }

        .footer-links {
            margin: 15px 0;
            padding: 0;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin: 0 10px;
            font-size: 13px;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .footer-divider {
            display: inline-block;
            margin: 0 8px;
            color: rgba(255, 255, 255, 0.3);
        }

        /* Alert Box */
        .alert {
            padding: 16px 20px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 14px;
        }

        .alert-info {
            background-color: #e3f2fd;
            border-left: 4px solid #2196f3;
            color: #0d47a1;
        }

        .alert-warning {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            color: #f57c00;
        }

        .alert-danger {
            background-color: #ffebee;
            border-left: 4px solid #f44336;
            color: #c62828;
        }

        .alert-success {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            color: #2e7d32;
        }

        /* QR Code Section */
        .qr-section {
            text-align: center;
            padding: 30px 20px;
            background-color: #f8f9fa;
            border-radius: 6px;
            margin: 20px 0;
        }

        .qr-section h3 {
            margin: 0 0 15px 0;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        .qr-section img {
            max-width: 200px;
            margin: 15px auto;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .qr-section p {
            margin: 10px 0 0 0;
            color: #666666;
            font-size: 13px;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                border-radius: 0 !important;
            }

            .email-header,
            .email-content,
            .email-footer {
                padding: 25px 20px !important;
            }

            .email-header h1 {
                font-size: 20px !important;
            }

            .button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
            }

            .info-table td {
                display: block;
                width: 100% !important;
                box-sizing: border-box;
            }

            .info-table td:first-child {
                border-bottom: none;
                padding-bottom: 4px;
            }

            .info-table td:last-child {
                padding-top: 4px;
            }
        }

        /* Dark Mode Support */
        @media (prefers-color-scheme: dark) {
            .email-container {
                background-color: #1a1a1a !important;
            }

            .email-content {
                color: #e0e0e0 !important;
            }

            .email-content h2,
            .greeting,
            .card-title {
                color: #ffffff !important;
            }

            .email-content p,
            .card-content {
                color: #b0b0b0 !important;
            }

            .card {
                background-color: #2a2a2a !important;
            }

            .info-table {
                background-color: #1a1a1a !important;
                border-color: #333333 !important;
            }

            .info-table td:first-child {
                background-color: #2a2a2a !important;
                color: #ffffff !important;
            }

            .info-table td:last-child {
                color: #b0b0b0 !important;
            }
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-container" width="600" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            @yield('header')
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td class="email-content">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            @yield('footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
