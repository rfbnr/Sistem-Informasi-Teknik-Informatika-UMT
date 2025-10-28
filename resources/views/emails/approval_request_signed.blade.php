@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Dokumen Terverifikasi & Ditandatangani! ✅',
        'subtitle' => 'Dokumen Anda telah diverifikasi dan ditandatangani secara resmi'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting" style="font-size: 16px; color: #2c3e50; font-weight: 500; margin-bottom: 20px;">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Success Message --}}
    <div class="alert alert-success" style="padding: 20px; border-radius: 6px; margin: 20px 0; font-size: 15px; background-color: #e8f5e9; border-left: 4px solid #4caf50; color: #2e7d32;">
        <strong>🎉 Selamat!</strong> Tanda tangan Anda telah <strong>DIVERIFIKASI</strong> oleh Kaprodi dan dokumen telah <strong>RESMI DITANDATANGANI</strong> secara digital. Dokumen siap digunakan!
    </div>

    {{-- Introduction --}}
    <p style="margin: 0 0 16px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Dokumen <strong>{{ $approvalRequest->document_name }}</strong> telah melalui <strong>seluruh proses verifikasi</strong> dan kini telah ditandatangani secara resmi. Kaprodi telah memverifikasi penempatan dan kualitas tanda tangan Anda, dan dokumen final telah dihasilkan.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Signature Information --}}
    @if(isset($documentSignature))
    <div class="card" style="background-color: #e8f5e9; border-left: 4px solid #4caf50; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            ✍️ Informasi Tanda Tangan Digital
        </h3>
        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px;">
            <tr>
                <td width="45%" style="color: #2c3e50; font-weight: 600;">Ditandatangani Oleh:</td>
                <td width="55%" style="color: #555555;">{{ $documentSignature->signer->name ?? 'Kaprodi' }}</td>
            </tr>
            <tr>
                <td style="color: #2c3e50; font-weight: 600;">Tanggal Tanda Tangan:</td>
                <td style="color: #555555;">{{ $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td style="color: #2c3e50; font-weight: 600;">Algoritma:</td>
                <td style="color: #555555;">{{ $documentSignature->digitalSignature->algorithm ?? 'RSA-SHA256' }}</td>
            </tr>
            <tr>
                <td style="color: #2c3e50; font-weight: 600;">Status:</td>
                <td>
                    <span class="badge badge-success" style="display: inline-block; padding: 4px 10px; font-size: 11px; font-weight: 600; border-radius: 4px; background-color: #d4edda; color: #155724;">
                        TERVERIFIKASI
                    </span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Complete Timeline --}}
    <div style="background-color: #f8f9fa; padding: 25px 20px; border-radius: 6px; margin: 25px 0;">
        <h4 style="margin: 0 0 20px 0; color: #2c3e50; font-size: 14px; font-weight: 600; text-align: center;">
            ✅ Proses Selesai - Semua Tahap Berhasil
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 10px; color: #4caf50; font-weight: 600;">DIAJUKAN</div>
                </td>

                {{-- Connector 1 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 10px; color: #4caf50; font-weight: 600;">DISETUJUI</div>
                </td>

                {{-- Connector 2 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 3: User Signed --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 10px; color: #4caf50; font-weight: 600;">DITANDATANGANI</div>
                </td>

                {{-- Connector 3 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div style="height: 3px; background: linear-gradient(to right, #4caf50 0%, #66bb6a 100%); width: 100%;"></div>
                </td>

                {{-- Step 4: Verified --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%); color: white; margin: 0 auto 8px auto; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px;">
                        ✓
                    </div>
                    <div style="font-size: 10px; color: #4caf50; font-weight: 600;">TERVERIFIKASI</div>
                </td>
            </tr>
        </table>

        <p style="margin: 15px 0 0 0; text-align: center; color: #4caf50; font-size: 13px; font-weight: 600;">
            🎊 Semua tahap telah berhasil diselesaikan!
        </p>
    </div>

    {{-- QR Code Section --}}
    @if(isset($qrCodeBase64) || isset($qrCodeUrl))
        @include('emails.components.qr-code', [
            'qrCodeBase64' => $qrCodeBase64 ?? null,
            'qrCodeUrl' => $qrCodeUrl ?? null,
            'verificationUrl' => $verificationUrl ?? null,
            'title' => '🔍 QR Code Verifikasi Dokumen'
        ])
    @endif

    {{-- Download Section --}}
    <div class="card" style="background-color: #e3f2fd; border-left: 4px solid #2196f3; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="card-title" style="margin: 0 0 15px 0; color: #2c3e50; font-size: 16px; font-weight: 600;">
            📥 Download Dokumen
        </h3>
        <p style="margin: 0 0 15px 0; color: #555555; font-size: 14px; line-height: 1.6;">
            Dokumen yang sudah ditandatangani tersedia dalam 2 cara:
        </p>
        <ol style="margin: 0; padding-left: 20px; color: #555555; font-size: 14px; line-height: 1.8;">
            <li style="margin-bottom: 8px;"><strong>Attachment Email</strong> - Cek lampiran email ini untuk file PDF yang sudah ditandatangani</li>
            <li style="margin-bottom: 0;"><strong>Download Manual</strong> - Klik tombol di bawah untuk download dari sistem</li>
        </ol>
    </div>

    {{-- Action Buttons --}}
    <div style="margin: 30px 0;">
        @include('emails.components.button', [
            'url' => route('user.signature.my.signatures.download', $approvalRequest->id),
            'text' => '📄 Download Dokumen Lengkap',
            'type' => 'primary',
            'block' => true
        ])

        @include('emails.components.button', [
            'url' => route('user.signature.approval.status'),
            'text' => '📊 Lihat Status & Riwayat',
            'type' => 'secondary',
            'block' => true
        ])
    </div>

    {{-- Divider --}}
    <div class="divider" style="height: 1px; background-color: #e9ecef; margin: 30px 0;"></div>

    {{-- Important Information --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 10px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            📌 Informasi Penting
        </h4>
        <ul style="margin: 0; padding-left: 20px; color: #666666; font-size: 13px; line-height: 1.8;">
            <li><strong>Dokumen Sah:</strong> Dokumen ini memiliki kekuatan hukum yang sama dengan dokumen bertanda tangan basah</li>
            <li><strong>QR Code:</strong> Gunakan QR Code untuk verifikasi keaslian dokumen kapan saja</li>
            <li><strong>Penyimpanan:</strong> Simpan dokumen ini dengan baik di tempat yang aman</li>
            <li><strong>Sharing:</strong> QR Code dapat dibagikan untuk verifikasi oleh pihak ketiga</li>
            <li><strong>Validitas:</strong> Dokumen ini valid selamanya kecuali ada pencabutan kunci digital</li>
        </ul>
    </div>

    {{-- Attachment Notice --}}
    <div class="alert alert-info" style="padding: 16px 20px; border-radius: 6px; margin: 20px 0; font-size: 14px; background-color: #e3f2fd; border-left: 4px solid #2196f3; color: #0d47a1;">
        <strong>📎 Lampiran Email:</strong> Email ini dilengkapi dengan:<br>
        • Dokumen PDF yang sudah ditandatangani<br>
        • QR Code verifikasi (file terpisah)
    </div>

    {{-- How to Verify Section --}}
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 6px; margin: 20px 0;">
        <h4 style="margin: 0 0 15px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
            🔐 Cara Verifikasi Keaslian Dokumen
        </h4>
        <table width="100%" cellpadding="10" cellspacing="0">
            <tr>
                <td width="60" align="center" valign="top" style="font-size: 24px;">1️⃣</td>
                <td style="color: #666666; font-size: 13px; line-height: 1.6;">
                    <strong>Scan QR Code</strong><br>
                    Buka kamera smartphone dan scan QR Code yang tersedia
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">2️⃣</td>
                <td style="color: #666666; font-size: 13px; line-height: 1.6;">
                    <strong>Akses Link Verifikasi</strong><br>
                    Atau kunjungi <a href="{{ route('signature.verify.page') }}" style="color: #667eea; text-decoration: none;">halaman verifikasi</a> dan masukkan nomor dokumen
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">3️⃣</td>
                <td style="color: #666666; font-size: 13px; line-height: 1.6;">
                    <strong>Lihat Detail Verifikasi</strong><br>
                    Sistem akan menampilkan detail tanda tangan dan validitas dokumen
                </td>
            </tr>
        </table>
    </div>

    {{-- Closing --}}
    <p style="margin: 30px 0 10px 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Terima kasih telah menggunakan sistem Digital Signature UMT Informatika. Semoga dokumen ini bermanfaat!
    </p>

    <p style="margin: 0; color: #555555; font-size: 15px; line-height: 1.6;">
        Hormat kami,<br>
        <strong>Tim Digital Signature</strong><br>
        <span style="color: #999999; font-size: 13px;">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
