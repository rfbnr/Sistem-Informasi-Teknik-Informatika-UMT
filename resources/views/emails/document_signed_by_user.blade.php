@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Dokumen Telah Ditandatangani ✍️',
        'subtitle' => 'Mahasiswa telah menandatangani dokumen dan menunggu verifikasi Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Yth. Ketua Program Studi,
    </p>

    {{-- Alert: Action Required --}}
    <div class="alert alert-warning">
        <strong style="font-size: 16px;">⏰ VERIFIKASI DIPERLUKAN</strong>
        <p class="mt-10 mb-0">
            Mahasiswa <strong>{{ $approvalRequest->user->name }}</strong> telah menyelesaikan proses penandatanganan dokumen. Dokumen ini memerlukan <strong>verifikasi Anda</strong> sebelum dapat difinalisasi.
        </p>
    </div>

    {{-- Introduction --}}
    <p>
        Dokumen dengan nama <strong>{{ $approvalRequest->document_name }}</strong> telah ditandatangani secara digital oleh mahasiswa menggunakan template tanda tangan yang telah dibuat. Mohon untuk melakukan verifikasi terhadap penempatan dan kualitas tanda tangan.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Signature Information --}}
    <div class="info-card-purple">
        <h3 class="section-title">
            ✍️ Informasi Penandatanganan
        </h3>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr class="info-row">
                <td class="info-label">
                    Mahasiswa
                </td>
                <td class="info-value">
                    {{ $approvalRequest->user->name }}
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">
                    Waktu Tanda Tangan
                </td>
                <td class="info-value">
                    {{ $documentSignature->signed_at ? $documentSignature->signed_at->format('d F Y, H:i') : '-' }} WIB
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">
                    Template Tanda Tangan
                </td>
                <td class="info-value">
                    {{ $documentSignature->signature_metadata['template_name'] ?? 'Template Default' }}
                </td>
            </tr>
            {{-- Template Creator --}}
            <tr class="info-row">
                <td class="info-label">
                    Dibuat Oleh
                </td>
                <td class="info-value">
                    {{ $documentSignature->signature_metadata['template_created_by'] ?? 'N/A' }}
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">
                    Status Dokumen
                </td>
                <td>
                    <span class="badge badge-warning">
                        Menunggu Verifikasi
                    </span>
                </td>
            </tr>
        </table>
    </div>

    {{-- Verification Guide --}}
    <div class="info-card-blue">
        <h3 class="section-title">
            📋 Panduan Verifikasi
        </h3>
        <p class="mb-12">
            Saat melakukan verifikasi, mohon periksa hal-hal berikut:
        </p>
        <ul class="list-styled">
            <li><strong>Penempatan tanda tangan:</strong> Pastikan tanda tangan berada pada posisi yang tepat</li>
            <li><strong>Kualitas visual:</strong> Tanda tangan terlihat jelas dan tidak buram</li>
            <li><strong>Ukuran proporsional:</strong> Ukuran tanda tangan sesuai dengan area dokumen</li>
            <li><strong>Tidak overlap:</strong> Tanda tangan tidak menutupi konten penting dokumen</li>
            <li><strong>Kesesuaian template:</strong> Tanda tangan sesuai dengan template yang disetujui</li>
        </ul>
    </div>

    {{-- Timeline Progress --}}
    <div class="timeline-container">
        <h4 class="timeline-title">
            📊 Progress Dokumen
        </h4>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                {{-- Step 1: Submitted --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DIAJUKAN</div>
                </td>

                {{-- Connector 1 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 2: Approved --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DISETUJUI</div>
                </td>

                {{-- Connector 2 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-complete"></div>
                </td>

                {{-- Step 3: Signed --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-complete">
                        ✓
                    </div>
                    <div class="timeline-label timeline-label-complete">DITANDATANGANI</div>
                </td>

                {{-- Connector 3 --}}
                <td width="20%" align="center" style="padding: 10px 0;">
                    <div class="timeline-connector timeline-connector-warning"></div>
                </td>

                {{-- Step 4: Verification (Current) --}}
                <td width="20%" align="center" style="padding: 10px 5px;">
                    <div class="timeline-step timeline-step-warning">
                        ⏳
                    </div>
                    <div class="timeline-label timeline-label-warning">VERIFIKASI</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Action Buttons --}}
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Verifikasi Tanda Tangan Dokumen
        </p>

        @include('emails.components.button', [
            'url' => route('admin.signature.dashboard'),
            'text' => '✅ Verifikasi & Review Dokumen',
            'type' => 'primary',
            'block' => true
        ])

        <p class="mt-15 mb-0 text-center">
            <a href="{{ route('admin.signature.dashboard') }}" class="link-primary">
                atau lihat semua dokumen yang menunggu verifikasi
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
            <li>Verifikasi dapat dilakukan kapan saja melalui dashboard Kaprodi</li>
            <li>Jika tanda tangan tidak sesuai, Anda dapat menolak dan meminta mahasiswa untuk menandatangani ulang</li>
            <li>Setelah diverifikasi, dokumen final akan otomatis dikirim ke mahasiswa dengan QR Code</li>
            <li>Proses verifikasi sebaiknya dilakukan dalam 1-2 hari kerja</li>
        </ul>
    </div>

    {{-- Closing --}}
    <p class="mt-30 mb-10">
        Terima kasih atas perhatian dan kerjasama Bapak/Ibu.
    </p>

    <p class="mb-0">
        Salam hormat,<br>
        <strong>Sistem Digital Signature</strong><br>
        <span class="text-muted text-small">UMT Informatika</span>
    </p>
@endsection

@section('footer')
    @include('emails.partials.footer')
@endsection
