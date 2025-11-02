@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Anda Disetujui! ✅',
        'subtitle' => 'Dokumen Anda telah disetujui dan siap untuk ditandatangani'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Success Message --}}
    <div class="alert alert-success">
        <strong>🎉 Selamat!</strong> Permintaan persetujuan dokumen Anda telah <strong>DISETUJUI</strong> oleh Ketua Program Studi.
    </div>

    {{-- Introduction --}}
    <p>
        Dokumen Anda dengan nama <strong>{{ $approvalRequest->document_name }}</strong> telah melalui proses review dan mendapatkan persetujuan. Langkah selanjutnya adalah proses <strong>penandatanganan digital</strong>.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- IMPORTANT: Next Action Required --}}
    <div class="alert alert-warning">
        <strong style="font-size: 16px;">⚡ TINDAKAN DIPERLUKAN</strong>
        <p class="mt-10 mb-0">
            Dokumen Anda telah disetujui oleh Kaprodi. Langkah selanjutnya adalah <strong>menempatkan QR Code verifikasi</strong> pada dokumen Anda sebelum proses penandatanganan digital dilakukan oleh sistem.
        </p>
    </div>

    {{-- How to Sign Section --}}
    <div class="info-card-blue">
        <h3 class="section-title">
            🎯 Langkah-Langkah Penandatanganan
        </h3>
        <ol class="list-styled">
            <li><strong>Klik tombol "Lanjutkan ke Penandatanganan"</strong> di bawah ini</li>
            <li><strong>Sistem akan menampilkan preview dokumen</strong> Anda dalam canvas interaktif</li>
            <li><strong>QR Code sementara akan muncul</strong> - Anda dapat drag & drop untuk menempatkannya di posisi yang diinginkan</li>
            <li><strong>Gunakan zoom controls</strong> untuk akurasi penempatan (jika dokumen multi-halaman, navigasi ke halaman yang sesuai)</li>
            <li><strong>Klik "Proses Tanda Tangan"</strong> setelah posisi QR Code sudah pas</li>
            <li><strong>Sistem akan otomatis:</strong>
                <ul style="margin-top: 8px;">
                    <li>Generate kunci digital RSA-2048 bit unik untuk dokumen Anda</li>
                    <li>Generate sertifikat X.509 v3 yang dipersonalisasi</li>
                    <li>Embed QR Code final ke dokumen pada posisi yang Anda pilih</li>
                    <li>Sign dokumen dengan tanda tangan digital CMS (RSA-SHA256)</li>
                    <li>Verifikasi otomatis (tidak perlu approval manual lagi)</li>
                </ul>
            </li>
        </ol>
    </div>

    {{-- Next Steps After Signing --}}
    <div class="section-card">
        <h4 class="section-subtitle">
            📌 Setelah Submit Penandatanganan
        </h4>
        <ul class="list-styled text-muted text-small">
            <li class="mb-6">Proses signing akan berjalan otomatis (< 10 detik)</li>
            <li class="mb-6">Dokumen akan <strong>langsung terverifikasi</strong> tanpa perlu approval manual</li>
            <li class="mb-6">Anda akan menerima <strong>email notifikasi</strong> berisi:
                <ul style="margin-top: 4px;">
                    <li>Dokumen PDF yang sudah ditandatangani (attachment)</li>
                    <li>QR Code verifikasi (attachment)</li>
                    <li>Link verifikasi publik</li>
                    <li>Informasi sertifikat digital</li>
                </ul>
            </li>
            <li>Dokumen final dilengkapi dengan <strong>QR Code</strong> yang dapat di-scan untuk verifikasi keaslian</li>
        </ul>
    </div>

    {{-- Timeline Indicator --}}
    <div class="timeline-container">
        <h4 class="timeline-title">
            📊 Progress Dokumen Anda
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DIAJUKAN</div>
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
                </td>

                {{-- Connector 2 --}}
                <td width="25%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-pending"></div>
                </td>

                {{-- Step 3: Signed (Pending) --}}
                <td width="25%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-pending">
                        ⏳
                    </div>
                    <div class="timeline-label timeline-label-pending">DITANDATANGANI</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Tandatangani Dokumen Anda Sekarang
        </p>

        @include('emails.components.button', [
            'url' => route('user.signature.sign.document', $approvalRequest->id),
            'text' => '🎯 Lanjutkan ke Penandatanganan',
            'type' => 'primary',
            'block' => true
        ])

        <p class="mt-15 mb-0 text-center">
            <a href="{{ route('user.signature.approval.status') }}" class="link-primary">
                atau lihat status dokumen Anda
            </a>
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider"></div>

    {{-- Important Notes --}}
    <div style="background-color: #fff3e0; border-left: 3px solid #ff9800; padding: 16px 20px; border-radius: 6px; margin: 20px 0;">
        <h4 class="section-subtitle">
            💡 Catatan Penting
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li>Simpan email ini sebagai bukti persetujuan dari Kaprodi</li>
            <li>Proses penandatanganan menggunakan kriptografi RSA-2048 bit yang aman</li>
            <li>Setiap dokumen mendapatkan kunci digital unik (tidak ada sharing key)</li>
            <li>Sertifikat X.509 v3 yang di-generate akan dipersonalisasi dengan informasi Kaprodi</li>
            <li>QR Code yang di-embed dapat di-scan oleh siapa saja untuk verifikasi publik</li>
        </ul>
    </div>

    {{-- Closing --}}
    <p class="mt-30 mb-10">
        Terima kasih telah menggunakan sistem Digital Signature UMT Informatika.
    </p>

    <p class="mb-0">
        Salam,<br>
        <strong>Tim Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
