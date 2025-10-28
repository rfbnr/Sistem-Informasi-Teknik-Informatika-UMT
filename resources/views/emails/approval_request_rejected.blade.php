@extends('emails.layouts.master')

@section('header')
    @include('emails.partials.header', [
        'title' => 'Permintaan Perlu Perbaikan',
        'subtitle' => 'Informasi mengenai permintaan persetujuan dokumen Anda'
    ])
@endsection

@section('content')
    {{-- Greeting --}}
    <p class="greeting">
        Halo {{ $approvalRequest->user->name }},
    </p>

    {{-- Alert Message --}}
    <div class="alert alert-warning">
        <strong>⚠️ Perhatian:</strong> Permintaan persetujuan dokumen Anda <strong>memerlukan perbaikan</strong>.
    </div>

    {{-- Introduction --}}
    <p>
        Setelah melakukan review, Ketua Program Studi memutuskan bahwa dokumen <strong>{{ $approvalRequest->document_name }}</strong> memerlukan beberapa perbaikan sebelum dapat disetujui.
    </p>

    {{-- Document Details Card --}}
    @include('emails.components.document-card', [
        'approvalRequest' => $approvalRequest,
        'showStatus' => true
    ])

    {{-- Rejection Reason --}}
    @if($approvalRequest->rejection_reason)
    <div style="background-color: #ffebee; border-left: 4px solid #f44336; border-radius: 6px; padding: 20px; margin: 25px 0;">
        <h3 class="section-title" style="color: #c62828;">
            📝 Alasan Penolakan
        </h3>
        <p class="mb-0" style="color: #c62828; background-color: #ffffff; padding: 15px; border-radius: 4px;">
            "{{ $approvalRequest->rejection_reason }}"
        </p>
    </div>
    @endif

    {{-- What to Do Next --}}
    <div class="info-card-blue">
        <h3 class="section-title">
            🔧 Langkah Perbaikan
        </h3>
        <ol class="list-styled">
            <li class="mb-8">Baca dengan teliti <strong>alasan penolakan</strong> di atas</li>
            <li class="mb-8">Lakukan <strong>perbaikan</strong> pada dokumen sesuai catatan yang diberikan</li>
            <li class="mb-8">Pastikan semua <strong>persyaratan</strong> dokumen sudah terpenuhi</li>
            <li class="mb-8"><strong>Upload ulang</strong> dokumen yang sudah diperbaiki</li>
            <li class="mb-0">Tunggu proses review berikutnya</li>
        </ol>
    </div>

    {{-- Action Button --}}
    <div class="mt-30">
        <p class="text-center text-strong mb-15">
            Siap Mengajukan Ulang?
        </p>

        @include('emails.components.button', [
            'url' => route('user.signature.approval.request'),
            'text' => '📤 Ajukan Dokumen Baru',
            'type' => 'primary',
            'block' => true
        ])

        <p class="mt-15 mb-0 text-center text-muted text-small">
            atau lihat
            <a href="{{ route('user.signature.approval.status') }}" class="link-primary">
                Status Semua Dokumen
            </a>
        </p>
    </div>

    {{-- Divider --}}
    <div class="divider"></div>

    {{-- Common Rejection Reasons & Tips --}}
    <div class="section-card">
        <h4 class="section-subtitle">
            💡 Tips Agar Dokumen Disetujui
        </h4>
        <ul class="list-no-margin text-muted text-small">
            <li>Pastikan format dokumen sesuai (PDF, ukuran max 25MB)</li>
            <li>Periksa kelengkapan informasi dalam dokumen</li>
            <li>Gunakan nama file yang jelas dan deskriptif</li>
            <li>Isi catatan tambahan jika ada informasi penting</li>
            <li>Hubungi kaprodi jika ada pertanyaan: <a href="mailto:{{ $approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id' }}" class="link-primary">{{ $approvalRequest->kaprodi->email ?? 'informatika@umt.ac.id' }}</a></li>
        </ul>
    </div>

    {{-- Rejection Details (if available) --}}
    @if($approvalRequest->rejected_at && $approvalRequest->rejector)
    <div style="background-color: #ffffff; border: 1px solid #e9ecef; padding: 15px 20px; border-radius: 6px; margin: 20px 0;">
        <table width="100%" cellpadding="5" cellspacing="0" class="text-small text-muted">
            <tr>
                <td width="40%" class="text-strong">Ditolak Oleh:</td>
                <td width="60%">{{ $approvalRequest->rejector->name }}</td>
            </tr>
            <tr>
                <td class="text-strong">Tanggal Penolakan:</td>
                <td>{{ $approvalRequest->rejected_at->format('d F Y, H:i') }} WIB</td>
            </tr>
        </table>
    </div>
    @endif

    {{-- Encouragement Message --}}
    <div class="alert alert-success">
        <strong>💪 Jangan Berkecil Hati!</strong> Penolakan ini adalah bagian dari proses quality control untuk memastikan dokumen Anda sempurna. Lakukan perbaikan dan ajukan kembali!
    </div>

    {{-- Closing --}}
    <p class="mt-30 mb-10">
        Jika ada pertanyaan atau butuh klarifikasi lebih lanjut, jangan ragu untuk menghubungi kami.
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
