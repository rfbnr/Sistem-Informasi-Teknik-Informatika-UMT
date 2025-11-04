@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Dokumen Berhasil Ditandatangani! ✅',
        'subtitle' => 'Dokumen Anda telah ditandatangani secara digital dan siap digunakan'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Success Message --}}
    <div class="alert alert-success">
        <strong>🎉 Selamat!</strong> Dokumen Anda telah <strong>BERHASIL DITANDATANGANI</strong> secara digital oleh sistem. Proses penandatanganan telah selesai dan dokumen sudah terverifikasi secara otomatis!
    </div>

    {{-- Introduction --}}
    <p>
        Dokumen <strong>{{ $approvalRequest->document_name }}</strong> telah melalui <strong>seluruh proses penandatanganan digital</strong> dengan sukses. Sistem telah membuat kunci RSA unik, generate sertifikat X.509, embed QR Code pada posisi yang Anda pilih, dan menandatangani dokumen menggunakan CMS signature (RSA-SHA256).
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
            🔐 Informasi Tanda Tangan Digital
        </h3>
        <table width="100%" cellpadding="8" cellspacing="0" style="font-size: 14px;">
            <tr>
                <td width="45%" class="text-strong">Ditandatangani Oleh:</td>
                <td width="55%">{{ $documentSignature->signer->name ?? 'Kaprodi Teknik Informatika' }}</td>
            </tr>
            <tr>
                <td class="text-strong">Tanggal Tanda Tangan:</td>
                <td>{{ $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y, H:i') : '-' }} WIB</td>
            </tr>
            <tr>
                <td class="text-strong">Algoritma Signature:</td>
                <td>{{ $documentSignature->digitalSignature->algorithm ?? 'RSA-SHA256' }} (CMS Format)</td>
            </tr>
            <tr>
                <td class="text-strong">Panjang Kunci:</td>
                <td>{{ $documentSignature->digitalSignature->key_length ?? '2048' }} bit</td>
            </tr>
            <tr>
                <td class="text-strong">Signature ID:</td>
                <td><code>{{ $documentSignature->digitalSignature->signature_id ?? '-' }}</code></td>
            </tr>
            <tr>
                <td class="text-strong">Sertifikat:</td>
                <td>X.509 v3 Self-Signed Certificate</td>
            </tr>
            <tr>
                <td class="text-strong">Berlaku Hingga:</td>
                <td>{{ $documentSignature->digitalSignature ? $documentSignature->digitalSignature->valid_until->format('d F Y') : '-' }} (3 tahun)</td>
            </tr>
            <tr>
                <td class="text-strong">Status:</td>
                <td>
                    <span class="badge badge-success">
                        ✅ TERVERIFIKASI & AKTIF
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
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DIAJUKAN</div>
                    <div class="timeline-sublabel">Oleh User</div>
                </td>

                {{-- Connector 1 --}}
                <td width="25%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DISETUJUI</div>
                    <div class="timeline-sublabel">Oleh Kaprodi</div>
                </td>

                {{-- Connector 2 --}}
                <td width="25%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 3: Signed & Verified (Auto) --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DITANDATANGANI</div>
                    <div class="timeline-sublabel">Otomatis</div>
                </td>
            </tr>
        </table>

        <p class="success-message">
            🎊 Semua tahap telah berhasil diselesaikan! Dokumen telah ditandatangani dan terverifikasi otomatis.
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
            📌 Informasi Penting Tanda Tangan Digital
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li><strong>Dokumen Sah:</strong> Dokumen ini ditandatangani dengan kriptografi RSA-2048 bit dan berlaku untuk keperluan internal UMT Informatika</li>
            <li><strong>Kunci Unik:</strong> Setiap dokumen memiliki kunci digital yang unik (tidak di-share dengan dokumen lain)</li>
            <li><strong>Sertifikat X.509:</strong> Dilengkapi dengan sertifikat digital standar industri yang dipersonalisasi</li>
            <li><strong>QR Code Verifikasi:</strong> Scan QR Code untuk memverifikasi keaslian dan integritas dokumen secara publik</li>
            <li><strong>CMS Signature:</strong> Menggunakan Cryptographic Message Syntax (RFC 5652) untuk signing</li>
            <li><strong>Document Hash:</strong> SHA-256 hash memastikan dokumen tidak bisa diubah tanpa terdeteksi</li>
            <li><strong>Validitas:</strong> Berlaku hingga {{ $documentSignature->digitalSignature ? $documentSignature->digitalSignature->valid_until->format('d F Y') : '3 tahun' }} (kecuali dicabut)</li>
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
            🔐 Cara Verifikasi Keaslian Dokumen (Publik)
        </h4>
        <table width="100%" cellpadding="10" cellspacing="0">
            <tr>
                <td width="60" align="center" valign="top" style="font-size: 24px;">1️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Scan QR Code</strong><br>
                    Buka kamera smartphone dan scan QR Code yang tercetak di dokumen. Link verifikasi akan terbuka otomatis.
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">2️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Sistem Verifikasi Otomatis</strong><br>
                    Sistem akan melakukan 7 checks: Token valid, Status signature, Key status, Key validity, CMS signature, Document hash match, dan Certificate validity.
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">3️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Lihat Hasil Verifikasi</strong><br>
                    Jika semua checks passed, dokumen akan menampilkan status VALID ✅ dengan detail lengkap tanda tangan digital dan sertifikat X.509.
                </td>
            </tr>
            <tr>
                <td align="center" valign="top" style="font-size: 24px;">4️⃣</td>
                <td class="text-muted text-small" style="line-height: 1.6;">
                    <strong>Verifikasi Manual (Alternatif)</strong><br>
                    Atau kunjungi <a href="{{ route('signature.verify.page') }}" class="link-primary">halaman verifikasi</a> dan masukkan token/URL secara manual.
                </td>
            </tr>
        </table>

        <div class="alert alert-info" style="margin-top: 15px;">
            <strong>🔒 Keamanan Publik:</strong> Siapa saja dapat memverifikasi dokumen, tetapi informasi sensitif (email, IP, serial number lengkap) di-mask untuk privasi.
        </div>
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
