{{-- QR Code Component --}}
{{--
    Usage:
    @include('emails.components.qr-code', [
        'qrCodeBase64' => $qrCodeBase64, // base64 encoded image
        'verificationUrl' => $verificationUrl,
        'title' => 'Scan QR Code untuk Verifikasi'
    ])
--}}

<div class="section-card text-center" style="padding: 30px 20px;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <h3 class="section-title">
                    {{ $title ?? '🔍 Verifikasi Dokumen' }}
                </h3>

                <p class="text-muted mb-20">
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
                    <p class="text-strong text-small mb-10">
                        📱 Cara Verifikasi:
                    </p>
                    <ol class="text-muted text-small" style="margin: 0; padding-left: 20px; text-align: left; line-height: 1.6;">
                        <li class="mb-6">Buka kamera smartphone Anda</li>
                        <li class="mb-6">Arahkan ke QR Code di atas</li>
                        <li class="mb-6">Klik link yang muncul</li>
                        <li class="mb-0">Lihat detail verifikasi dokumen</li>
                    </ol>
                </div>

                {{-- Alternative Link --}}
                @if(isset($verificationUrl))
                <p class="mt-15 mb-0 text-muted text-tiny">
                    Atau klik link berikut untuk verifikasi manual:<br>
                    <a href="{{ $verificationUrl }}" class="link-primary" style="font-weight: 600;">
                        Verifikasi Dokumen
                    </a>
                </p>
                @endif

                {{-- Security Note --}}
                <div class="info-card-blue" style="text-align: left; margin: 20px 0;">
                    <p class="mb-0 text-tiny" style="color: #0d47a1; line-height: 1.5;">
                        <strong>🔒 Catatan Keamanan:</strong><br>
                        QR Code ini unik untuk dokumen Anda dan dapat digunakan oleh siapa saja untuk memverifikasi keaslian dokumen. Jangan bagikan informasi verifikasi sensitif lainnya.
                    </p>
                </div>
            </td>
        </tr>
    </table>
</div>
