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
            Dokumen Anda telah disetujui, tetapi Anda perlu <strong>MENANDATANGANI dokumen secara manual</strong> menggunakan template tanda tangan digital yang telah Kaprodi sediakan sebelumnya.
        </p>
    </div>

    {{-- How to Sign Section --}}
    <div class="info-card-blue">
        <h3 class="section-title">
            ✍️ Cara Menandatangani Dokumen
        </h3>
        <ol class="list-styled">
            <li><strong>Klik tombol "Tandatangani Dokumen"</strong> di bawah ini untuk membuka halaman penandatanganan</li>
            <li><strong>Pilih template tanda tangan Kaprodi</strong> yang sudah disediakan</li>
            <li><strong>Letakkan tanda tangan</strong> pada posisi yang sesuai di dokumen (drag & drop)</li>
            <li><strong>Review penempatan</strong> tanda tangan Anda sebelum submit</li>
            <li><strong>Submit untuk review</strong> - Kaprodi akan memverifikasi tanda tangan Anda</li>
        </ol>
    </div>

    {{-- Next Steps After Signing --}}
    <div class="section-card">
        <h4 class="section-subtitle">
            📌 Setelah Menandatangani
        </h4>
        <ul class="list-styled text-muted text-small">
            <li class="mb-6">Kaprodi akan menerima notifikasi untuk memverifikasi penempatan tanda tangan tersebut</li>
            <li class="mb-6">Proses verifikasi biasanya memakan waktu 1-2 hari kerja</li>
            <li class="mb-6">Anda akan menerima email notifikasi setelah dokumen diverifikasi</li>
            <li>Dokumen final akan dilengkapi dengan <strong>QR Code verifikasi</strong></li>
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
            'text' => '✍️ Tandatangani Dokumen',
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
            <li>Simpan email ini sebagai bukti persetujuan</li>
            <li>Cek halaman status secara berkala untuk update terbaru</li>
            <li>Setelah ditandatangani, Anda akan menerima dokumen final dengan QR Code</li>
            <li>QR Code dapat digunakan untuk verifikasi keaslian dokumen</li>
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
