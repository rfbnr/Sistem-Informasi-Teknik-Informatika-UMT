{{-- QR Code Component --}}
{{--
    Usage:
    @include('emails.components.qr-code', [
        'qrCodeBase64' => $qrCodeBase64, // base64 encoded image
        'verificationUrl' => $verificationUrl,
        'title' => 'Scan QR Code untuk Verifikasi'
    ])
--}}

<div class="qr-section" style="text-align: center; padding: 30px 20px; background-color: #f8f9fa; border-radius: 6px; margin: 20px 0;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <h3 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
                    {{ $title ?? '🔍 Verifikasi Dokumen' }}
                </h3>

                <p style="margin: 0 0 20px 0; color: #666666; font-size: 14px; line-height: 1.6;">
                    Scan QR Code di bawah ini untuk memverifikasi keaslian dokumen
                </p>

                {{-- QR Code Image --}}
                @if(isset($qrCodeUrl))
                    <img src="{{ $qrCodeUrl }}" alt="QR Code Verifikasi Url" style="max-width: 200px; margin: 15px auto; padding: 15px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); display: block;" />
                @elseif(isset($qrCodeBase64))
                    <img src="data:image/png;base64,{{ $qrCodeBase64 }}" alt="QR Code Verifikasi Base64" style="max-width: 200px; margin: 15px auto; padding: 15px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); display: block;" />
                @endif

                {{-- Instructions --}}
                <div style="background-color: #ffffff; border-radius: 6px; padding: 15px; margin: 20px 0; border: 1px solid #e9ecef;">
                    <p style="margin: 0 0 10px 0; color: #2c3e50; font-size: 13px; font-weight: 600;">
                        📱 Cara Verifikasi:
                    </p>
                    <ol style="margin: 0; padding-left: 20px; text-align: left; color: #666666; font-size: 13px; line-height: 1.6;">
                        <li style="margin-bottom: 6px;">Buka kamera smartphone Anda</li>
                        <li style="margin-bottom: 6px;">Arahkan ke QR Code di atas</li>
                        <li style="margin-bottom: 6px;">Klik link yang muncul</li>
                        <li style="margin-bottom: 0;">Lihat detail verifikasi dokumen</li>
                    </ol>
                </div>

                {{-- Alternative Link --}}
                @if(isset($verificationUrl))
                <p style="margin: 15px 0 0 0; color: #999999; font-size: 12px;">
                    Atau klik link berikut untuk verifikasi manual:<br>
                    <a href="{{ $verificationUrl }}" style="color: #667eea; text-decoration: none; font-weight: 600;">
                        Verifikasi Dokumen
                    </a>
                </p>
                @endif

                {{-- Security Note --}}
                <div style="background-color: #e3f2fd; border-left: 3px solid #2196f3; padding: 12px 15px; margin: 20px 0; border-radius: 4px; text-align: left;">
                    <p style="margin: 0; color: #0d47a1; font-size: 12px; line-height: 1.5;">
                        <strong>🔒 Catatan Keamanan:</strong><br>
                        QR Code ini unik untuk dokumen Anda dan dapat digunakan oleh siapa saja untuk memverifikasi keaslian dokumen. Jangan bagikan informasi verifikasi sensitif lainnya.
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>
