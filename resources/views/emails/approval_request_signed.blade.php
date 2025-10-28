@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Dokumen Terverifikasi & Ditandatangani! ✅',
        'subtitle' => 'Dokumen Anda telah diverifikasi dan ditandatangani secara resmi'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Success Message --}}
    <div class="alert alert-success">
        <strong>🎉 Selamat!</strong> Tanda tangan Anda telah <strong>DIVERIFIKASI</strong> oleh Kaprodi dan dokumen telah <strong>RESMI DITANDATANGANI</strong> secara digital. Dokumen siap digunakan!
    </div>

    {{-- Introduction --}}
    <p>
        Dokumen <strong>{{ $approvalRequest->document_name }}</strong> telah melalui <strong>seluruh proses verifikasi</strong> dan kini telah ditandatangani secara resmi. Kaprodi telah memverifikasi penempatan dan kualitas tanda tangan Anda, dan dokumen final telah dihasilkan.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Signature Information --}}
    @if(isset($documentSignature))
    <div class="info-card-green">
        <h3 class="section-title">
            ✍️ Informasi Tanda Tangan Digital
        </h3>
        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px;">
            <tr>
                <td width="45%" class="text-strong">Ditandatangani Oleh:</td>
                <td width="55%">{{ $documentSignature->signer->name ?? 'Kaprodi' }}</td>
            </tr>
            <tr>
                <td class="text-strong">Tanggal Tanda Tangan:</td>
                <td>{{ $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td class="text-strong">Algoritma:</td>
                <td>{{ $documentSignature->digitalSignature->algorithm ?? 'RSA-SHA256' }}</td>
            </tr>
            <tr>
                <td class="text-strong">Status:</td>
                <td>
                    <span class="badge badge-success">
                        TERVERIFIKASI
                    </span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Complete Timeline --}}
    <div class="timeline-container">
        <h4 class="timeline-title">
            ✅ Proses Selesai - Semua Tahap Berhasil
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DIAJUKAN</div>
                </td>

                {{-- Connector 1 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DISETUJUI</div>
                </td>

                {{-- Connector 2 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 3: User Signed --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DITANDATANGANI</div>
                </td>

                {{-- Connector 3 --}}
                <td width="16%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 4: Verified --}}
                <td width="16%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">TERVERIFIKASI</div>
                </td>
            </tr>
        </table>

        <p class="success-message">
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
    <div class="info-card-blue">
        <h3 class="section-title">
            📥 Download Dokumen
        </h3>
        <p class="mb-15">
            Dokumen yang sudah ditandatangani tersedia dalam 2 cara:
        </p>
        <ol class="list-styled">
            <li class="mb-8"><strong>Attachment Email</strong> - Cek lampiran email ini untuk file PDF yang sudah ditandatangani</li>
            <li><strong>Download Manual</strong> - Klik tombol di bawah untuk download dari sistem</li>
        </ol>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-30">
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
    <div class="divider"></div>

    {{-- Important Information --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 class="section-subtitle">
            📌 Informasi Penting
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li><strong>Dokumen Sah:</strong> Dokumen ini memiliki kekuatan hukum yang sama dengan dokumen bertanda tangan basah dan dokumen ini hanya berlaku untuk keperluan internal UMT Informatika</li>
            <li><strong>QR Code:</strong> Gunakan QR Code untuk verifikasi keaslian dokumen kapan saja</li>
            <li><strong>Penyimpanan:</strong> Simpan dokumen ini dengan baik di tempat yang aman</li>
            <li><strong>Sharing:</strong> QR Code dapat dibagikan untuk verifikasi oleh pihak ketiga</li>
            <li><strong>Validitas:</strong> Dokumen ini valid selamanya kecuali ada pencabutan kunci digital</li>
        </ul>
    </div>

    {{-- Attachment Notice --}}
    <div class="alert alert-info">
        <strong>📎 Lampiran Email:</strong> Email ini dilengkapi dengan:<br>
        • Dokumen PDF yang sudah ditandatangani<br>
        • QR Code verifikasi (file terpisah)
    </div>

    {{-- How to Verify Section --}}
    <div class="section-card">
        <h4 class="section-subtitle">
            🔐 Cara Verifikasi Keaslian Dokumen
        </h4>
        <table width="100%" cellpadding="10" cellspacing="0">
            <tr>
                <td width="60" align="center" valign="top" style="font-size: 24px;">1️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Scan QR Code</strong><br>
                    Buka kamera smartphone dan scan QR Code yang tersedia
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">2️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Akses Link Verifikasi</strong><br>
                    Atau kunjungi <a href="{{ route('signature.verify.page') }}" class="link-primary">halaman verifikasi</a> dan masukkan nomor dokumen
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">3️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Lihat Detail Verifikasi</strong><br>
                    Sistem akan menampilkan detail tanda tangan dan validitas dokumen
                </td>
            </tr>
        </table>
    </div>

    {{-- Closing --}}
    <p class="mt-30 mb-10">
        Terima kasih telah menggunakan sistem Digital Signature UMT Informatika. Semoga dokumen ini bermanfaat!
    </p>

    <p class="mb-0">
        Hormat kami,<br>
        <strong>Tim Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
